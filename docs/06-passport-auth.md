# Passport and sign-in

**What it is:** Laravel Passport creates and checks **access tokens**: long random keys that prove who is calling the API. It's the same sign-in setup as Navitrack.

**Where we use it:** `AuthController` (sign in / me / sign out), `AddTokenFromCookie` middleware, `config/auth.php` (the `api` guard), `AuthServiceProvider` (how long tokens last), and `User` (`HasApiTokens`).

Official docs: https://laravel.com/docs/10.x/passport

---

## The whole flow

```
1. React   POST /api/auth/login { email, password }
2. API     checks the password -> $user->createToken('web') -> a token
           answers with the user + Set-Cookie: auth_token=<token>; HttpOnly; SameSite=Strict
3. Browser stores the cookie and sends it with every request to the API
4. API     AddTokenFromCookie: cookie -> "Authorization: Bearer <token>"
           auth:api (Passport) checks the token -> $request->user() is that user
5. React   POST /api/logout -> the token is revoked, the cookie removed
```

**Why a cookie and not localStorage?** An `HttpOnly` cookie can't be read by JavaScript, so if a bad script ever runs on the page, it can't steal the token. `SameSite=Strict` stops other websites from sending it.

## Protecting routes

```php
Route::middleware('auth:api')->group(function () {
    // everything here needs a valid token, otherwise 401 "Please sign in."
});
```

## Who is signed in?

```php
$request->user();          // the User, inside any auth:api route
$request->user()->id;
auth()->id();              // the same id, anywhere
```

Use it to record who did something:

```php
$order = OrderModel::create([...$data, 'created_by' => $request->user()->id]);
```

## Token lifetime

`TOKEN_EXPIRY_HOURS` in `.env` (default 12) sets how long a sign-in lasts. It's used in two places:
- `AuthServiceProvider`: `Passport::personalAccessTokensExpireIn(...)`, so the token stops working.
- `AuthController`: the cookie expires at the same time.

When it runs out, the API answers 401 and the React app goes back to the login page.

## The pieces Passport needs

| Piece | Where | Made by |
| --- | --- | --- |
| encryption keys | `storage/oauth-private.key`, `oauth-public.key` | `php artisan passport:keys` |
| a "personal access client" | the `oauth_personal_access_clients` table | `php artisan passport:client --personal` |
| tokens | the `oauth_access_tokens` table | `createToken()` at each sign-in |

Our `DatabaseSeeder` makes the first two for you. The keys are secret: they're in `.gitignore`, and each server makes its own.

## Problems

| Symptom | Fix |
| --- | --- |
| "Personal access client not found" | `php artisan db:seed` (or `php artisan passport:client --personal`) |
| "Key path ... does not exist" | `php artisan passport:keys` |
| sign-in works but the next request is 401 | open both apps on `localhost` (not `127.0.0.1`), so the cookie is sent |
| everyone signed out after `migrate:fresh` | expected: the tokens table was emptied |

## Adding roles later (not in this kit)

Keep it simple first: add a `role` column to `users`, then check it in a controller:

```php
if ($request->user()->role !== 'admin') {
    return $this->dataResponse('error', 403, 'Only admins can do this.');
}
```

Navitrack uses a full permissions system. Learn this kit first.
