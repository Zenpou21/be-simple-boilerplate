# Laravel basics

**What it is:** a PHP framework that does the routine work of an API: matching URLs, reading requests, validating input, talking to the database and sending answers. This kit uses **Laravel 10**.

Official docs (search at the top, version 10.x): https://laravel.com/docs/10.x

---

## The journey of one request

```
GET /api/products/12
  1. public/index.php           every request enters here
  2. app/Http/Kernel.php        middleware for the 'api' group runs (JSON, cookie -> token, rate limit)
  3. routes/api.php             'products/{product}' -> ProductController@onGetOne
  4. 'auth:api' middleware      no valid token? -> 401, stop
  5. route model binding        {product}=12 -> ProductModel::findOrFail(12), or 404
  6. the controller method      does the work, returns dataResponse(...)
  7. JSON back to the browser
```

## Routes (`routes/api.php`)

Every URL in this file starts with `/api`.

```php
Route::get('me', [AuthController::class, 'onGetUser']);           // GET /api/me

Route::prefix('products')->controller(ProductController::class)->group(function () {
    Route::get('list', 'onGetList');          // GET    /api/products/list
    Route::get('{product}', 'onGetOne');      // GET    /api/products/12
    Route::post('/', 'onCreate');             // POST   /api/products
    Route::put('{product}', 'onUpdate');      // PUT    /api/products/12
    Route::delete('{product}', 'onDelete');   // DELETE /api/products/12
});
```

- **Order matters.** Put `list` before `{product}`, or "list" is treated as an id.
- **Route model binding:** `{product}` in the URL plus `ProductModel $product` in the method means Laravel loads the row for you. The two names must match.
- See all routes with `php artisan route:list --path=api`.

## Controllers

A controller groups the methods for one module. Our naming follows Navitrack: `onGetList`, `onGetOne`, `onCreate`, `onUpdate`, `onDelete`.

## The Request object

```php
public function onCreate(Request $request)
{
    $request->input('name');               // one value from the JSON body or the query string
    $request->input('stock', 0);           // with a default
    $request->query('page');               // only from the query string (?page=2)
    $request->has('description');          // was it sent?
    $request->user();                      // the signed-in user (see the Passport guide)

    $data = $request->validate([...]);     // check the rules, get only the validated fields
}
```

Use `validate()` for anything you save. See [validation](03-validation.md).

## Answers

Every answer uses our `ResponseTrait`, so the React app reads them all the same way:

```php
return $this->dataResponse('success', 200, 'Product found.', $product);
return $this->dataResponse('success', 201, 'Product created.', $product);
return $this->dataResponse('error', 404, 'Record not found.');
return $this->dataResponse('error', 403, 'You can\'t delete a product that has orders.');
```

The `message` becomes the toast in the React app, so write it for the user.

| Code | When |
| --- | --- |
| 200 | OK |
| 201 | created |
| 401 | not signed in |
| 403 | signed in but not allowed |
| 404 | not found |
| 422 | the input failed validation (Laravel sends this by itself) |
| 500 | a bug: check `storage/logs/laravel.log` |

## Middleware

Middleware runs before the controller and can stop the request. Ours, in `app/Http/Middleware/`:
- `ForceJsonResponse` makes every answer JSON, even errors.
- `AddTokenFromCookie` turns the `auth_token` cookie into an `Authorization: Bearer` header.

They're registered for every API route in the `'api'` group in `app/Http/Kernel.php`. Add one to a route with `->middleware('auth:api')` or `->middleware('throttle:10,1')`.

## Settings: `.env` and `config/`

- `.env` holds values that differ per computer or server (database, URLs, secrets). It is never committed.
- `config/*.php` reads them with `env('NAME', 'default')`. Your code then reads the config:

```php
config('auth.token_expiry_hours')   // from config/auth.php, which reads TOKEN_EXPIRY_HOURS
```

Only call `env()` inside `config/` files, because on a server where the config is cached, `env()` elsewhere returns null. After changing `.env`, run `php artisan config:clear` if the change doesn't show up.

## Logs and errors

- Errors go to `storage/logs/laravel.log`, the first place to look when you get a 500.
- Write your own lines with `Log::info('Saving product', ['id' => $product->id]);` (`use Illuminate\Support\Facades\Log;`).
- `APP_DEBUG=true` in `.env` shows the error details in the response. Always use `false` on a real server.
- Friendly 401/404 messages are set in `app/Exceptions/Handler.php`.
