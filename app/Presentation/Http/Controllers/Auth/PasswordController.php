<?php

namespace App\Presentation\Http\Controllers\Auth;

use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Auth\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->newPassword()),
        ]);

        return back();
    }
}
