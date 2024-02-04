<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class AuthenticateByProviderController extends Controller
{
    public function redirect(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        $providerUser = Socialite::driver($provider)->user();

        $user = User::where('email', $providerUser->getEmail())->first();

        if ($user == null) {
            return Redirect::route('login')->with([
                'status' => 'These credentials do not match our records.',
            ]);
        } elseif ($user->active === false) {
            return Redirect::route('login')->with([
                'status' => 'This account is inactivated.',
            ]);
        } else {
            $user = User::updateOrCreate([
                'email' => $providerUser->getEmail(),
            ], [
                'name' => $providerUser->getName(),
                'provider_name' => $provider,
                'provider_id' => $providerUser->getId(),
                'provider_avatar' => $providerUser->getAvatar(),
                'provider_token' => $providerUser->token,
                'provider_refresh_token' => $providerUser->refreshToken,
            ]);

            event(new Registered($user));

            Auth::login($user);

            return redirect('/dashboard');
        }
    }
}
