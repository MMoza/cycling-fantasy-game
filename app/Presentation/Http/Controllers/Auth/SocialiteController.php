<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController
{
    public function redirect(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        $socialUser = Socialite::driver($provider)->user();

        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            if (! $user->google_id) {
                $user->update([
                    'google_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                ]);
            }

            Auth::login($user);

            return redirect()->intended(route('dashboard', absolute: false));
        }

        $user = User::where('google_id', $socialUser->getId())->first();

        if ($user) {
            Auth::login($user);

            return redirect()->intended(route('dashboard', absolute: false));
        }

        $user = User::create([
            'id' => Str::uuid()->toString(),
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'google_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'email_verified_at' => now(),
        ]);

        Auth::login($user);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
