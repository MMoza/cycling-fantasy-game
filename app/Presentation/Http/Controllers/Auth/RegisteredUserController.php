<?php

namespace App\Presentation\Http\Controllers\Auth;

use App\Application\UseCases\Invitation\IncrementInvitationCountUseCase;
use App\Infrastructure\Persistence\Models\InvitationModel;
use App\Models\User;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Auth\RegisterRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly IncrementInvitationCountUseCase $incrementInvitationCountUseCase,
    ) {}

    /**
     * Display the registration view.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/Register', [
            'invitationRef' => $request->query('ref'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {

        $invitedBy = null;

        if ($request->referralCode()) {
            $invitation = InvitationModel::where('code', $request->referralCode())->first();

            if ($invitation !== null) {
                $invitedBy = $invitation->user_id;
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'invited_by' => $invitedBy,
        ]);

        if ($invitedBy !== null && $request->referralCode()) {
            $this->incrementInvitationCountUseCase->execute($request->referralCode());
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
