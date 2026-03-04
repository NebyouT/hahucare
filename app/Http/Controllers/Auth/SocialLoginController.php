<?php

namespace App\Http\Controllers\Auth;

use App\Events\Frontend\UserRegistered;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProvider;
use App\Providers\RouteServiceProvider;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    public function redirectTo()
    {
        $redirectTo = request()->redirectTo;

        if ($redirectTo) {
            return $redirectTo;
        } else {
            return RouteServiceProvider::HOME;
        }
    }

    /**
     * Redirect the user to the Provider (Facebook, Google, GitHub...) authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from Provider (Facebook, Google, GitHub...).
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($provider)
    {
        try {
            $user = Socialite::driver($provider)->user();

            $authUser = $this->findOrCreateUser($user, $provider);

            if ($authUser instanceof \Illuminate\Http\RedirectResponse) {
                return $authUser;
            }

            if (!$authUser instanceof User) {
                Log::error('Social Login Error: findOrCreateUser did not return a User', [
                    'provider' => $provider,
                    'returned_type' => gettype($authUser),
                ]);
                flash('Login failed. Please try again.')->error()->important();
                return redirect('/admin/login');
            }

            Auth::login($authUser, true);
        } catch (Exception $e) {
            Log::error('Social Login Exception', [
                'provider' => $provider,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            flash('Google login failed: ' . $e->getMessage())->error()->important();
            return redirect('/admin/login');
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Return user if exists; create and return if doesn't.
     *
     * @param $githubUser
     * @return User
     */
    private function findOrCreateUser($socialUser, $provider)
    {
        if ($authUser = UserProvider::where('provider_id', $socialUser->getId())->first()) {
            $authUser = User::findOrFail($authUser->user->id);

            return $authUser;
        } elseif ($authUser = User::where('email', $socialUser->getEmail())->first()) {
            UserProvider::create([
                'user_id' => $authUser->id,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
                'provider' => $provider,
            ]);

            return $authUser;
        } else {
            $name = $socialUser->getName();

            $name_parts = $this->split_name($name);
            $first_name = $name_parts[0];
            $last_name = $name_parts[1];
            $email = $socialUser->getEmail();

            if ($email == '' || $email === null) {
                Log::warning('Social Login: No email provided by provider', [
                    'provider' => $provider,
                    'social_user_id' => $socialUser->getId(),
                ]);
                flash('Email address is required! Please use a Google account with an email address.')->error()->important();
                return redirect('/admin/login');
            }

            $user = User::create([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'name' => $name,
                'email' => $email,
            ]);

            $media = $user->addMediaFromUrl($socialUser->getAvatar())->toMediaCollection('users');
            $user->avatar = $media->getUrl();
            $user->save();

            event(new UserRegistered($user));

            UserProvider::create([
                'user_id' => $user->id,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
                'provider' => $provider,
            ]);

            return $user;
        }
    }

    /**
     * Split Name into first name and last name.
     */
    public function split_name($name)
    {
        $name = trim($name);

        $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $first_name = trim(preg_replace('#'.$last_name.'#', '', $name));

        return [$first_name, $last_name];
    }
}
