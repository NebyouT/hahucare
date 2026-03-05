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
        Log::info('Social Login Callback Started', [
            'provider' => $provider,
            'url' => request()->fullUrl(),
            'has_code' => request()->has('code'),
            'has_error' => request()->has('error'),
        ]);

        try {
            Log::info('Attempting to get user from Socialite');
            $user = Socialite::driver($provider)->user();

            Log::info('Socialite user retrieved', [
                'provider' => $provider,
                'social_id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
            ]);

            $authUser = $this->findOrCreateUser($user, $provider);

            if ($authUser instanceof \Illuminate\Http\RedirectResponse) {
                Log::warning('findOrCreateUser returned RedirectResponse');
                return $authUser;
            }

            if (!$authUser instanceof User) {
                Log::error('Social Login Error: findOrCreateUser did not return a User', [
                    'provider' => $provider,
                    'returned_type' => gettype($authUser),
                    'returned_value' => $authUser,
                ]);
                flash('Login failed. Please try again.')->error()->important();
                return redirect('/admin/login');
            }

            Log::info('Attempting to login user', [
                'user_id' => $authUser->id,
                'email' => $authUser->email,
            ]);

            Auth::login($authUser, true);

            Log::info('User logged in successfully', [
                'user_id' => $authUser->id,
                'email' => $authUser->email,
                'authenticated' => Auth::check(),
            ]);
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

        Log::info('Redirecting to home', [
            'home' => RouteServiceProvider::HOME,
        ]);

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
        Log::info('findOrCreateUser started', [
            'provider' => $provider,
            'social_id' => $socialUser->getId(),
            'email' => $socialUser->getEmail(),
        ]);

        // Check if user already linked via UserProvider
        if ($authUser = UserProvider::where('provider_id', $socialUser->getId())->first()) {
            Log::info('User found via UserProvider', [
                'user_provider_id' => $authUser->id,
                'user_id' => $authUser->user_id,
            ]);

            $authUser = User::findOrFail($authUser->user->id);

            Log::info('Returning existing user from UserProvider', [
                'user_id' => $authUser->id,
                'email' => $authUser->email,
            ]);

            return $authUser;
        } elseif ($authUser = User::where('email', $socialUser->getEmail())->first()) {
            Log::info('User found by email, linking to provider', [
                'user_id' => $authUser->id,
                'email' => $authUser->email,
            ]);

            UserProvider::create([
                'user_id' => $authUser->id,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
                'provider' => $provider,
            ]);

            Log::info('UserProvider link created, returning user', [
                'user_id' => $authUser->id,
            ]);

            return $authUser;
        } else {
            Log::info('User not found, creating new user');

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

            Log::info('Creating new user', [
                'email' => $email,
                'name' => $name,
            ]);

            try {
                $user = User::create([
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'name' => $name,
                    'email' => $email,
                ]);

                Log::info('User created', [
                    'user_id' => $user->id,
                ]);

                $media = $user->addMediaFromUrl($socialUser->getAvatar())->toMediaCollection('users');
                $user->avatar = $media->getUrl();
                $user->save();

                Log::info('User avatar saved');

                event(new UserRegistered($user));

                Log::info('UserRegistered event fired');

                UserProvider::create([
                    'user_id' => $user->id,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'provider' => $provider,
                ]);

                Log::info('UserProvider created, returning new user', [
                    'user_id' => $user->id,
                ]);

                return $user;
            } catch (\Exception $e) {
                Log::error('Error creating user', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                throw $e;
            }
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
