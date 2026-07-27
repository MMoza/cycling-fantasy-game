<?php

namespace App\Presentation\Http\Controllers\Auth;

use App\Application\UseCases\Invitation\IncrementInvitationCountUseCase;
use App\Infrastructure\Persistence\Models\InvitationModel;
use App\Models\User;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
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
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'ref' => 'nullable|string|size:12',
        ]);

        $invitedBy = null;

        if ($request->ref) {
            $invitation = InvitationModel::where('code', $request->ref)->first();

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

        if ($invitedBy !== null && $request->ref) {
            $this->incrementInvitationCountUseCase->execute($request->ref);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
