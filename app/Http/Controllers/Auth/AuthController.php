<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Sign in, who am I, sign out.
 *
 * The token is put in an HttpOnly cookie: the browser stores it and sends it back with every
 * request, and JavaScript can't read it (so a malicious script can't steal it).
 * AddTokenFromCookie turns the cookie back into a token for Passport.
 */
class AuthController extends Controller
{
    use ResponseTrait;

    /** POST /api/auth/login  { email, password } */
    public function onLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return $this->dataResponse('error', 401, 'The email or password is incorrect.');
        }

        // A Passport token that ends after TOKEN_EXPIRY_HOURS (see AuthServiceProvider).
        $token = $user->createToken('web')->accessToken;

        return $this->dataResponse('success', 200, "Welcome back, {$user->name}!", $user)
            ->cookie($this->tokenCookie($request, $token, config('auth.token_expiry_hours') * 60));
    }

    /** GET /api/me  -> the signed-in user (the frontend asks this on page load). */
    public function onGetUser(Request $request)
    {
        return $this->dataResponse('success', 200, 'Signed in.', $request->user());
    }

    /** POST /api/logout  -> the token stops working and the cookie is removed. */
    public function onLogout(Request $request)
    {
        $request->user()->token()->revoke();

        return $this->dataResponse('success', 200, 'Signed out.')
            ->withCookie(cookie()->forget('auth_token'));
    }

    private function tokenCookie(Request $request, string $token, int $minutes)
    {
        return cookie(
            name: 'auth_token',
            value: $token,
            minutes: $minutes,
            path: '/',
            domain: null,
            secure: $request->isSecure(), // HTTPS only when the site uses HTTPS (always, on a real server)
            httpOnly: true,               // JavaScript can't read it
            raw: false,
            sameSite: 'strict'            // other websites can't send it
        );
    }
}
