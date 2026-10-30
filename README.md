# Starter Kit API

The backend for the **Starter Kit** app (the React app is in the `starter-kit` folder next to this one).
It uses the same setup as Navitrack, kept small so it is easy to learn from.

| What | Used for |
| --- | --- |
| **Laravel 10** (PHP 8.1+) | the API framework |
| **Laravel Passport** | sign-in tokens |
| **MySQL** | the database |

**New to any of these?** [`docs/`](docs/README.md) has a short guide for each tool (PHP, Laravel, validation, Eloquent, migrations, Passport, Artisan, MySQL, Postman), with examples from this kit.

---

## 1. Setup (first time)

You need PHP 8.1 or newer, Composer and MySQL. [Laragon](https://laragon.org) on Windows gives you all three.

```bash
composer install
cp .env.example .env          # Windows cmd: copy .env.example .env
php artisan key:generate
```

Create an empty MySQL database called `starter_kit` (in HeidiSQL, phpMyAdmin, or `mysql -uroot -e "CREATE DATABASE starter_kit"`).
If your MySQL user is not `root` with no password, change `DB_USERNAME` / `DB_PASSWORD` in `.env`.

```bash
php artisan migrate --seed    # creates the tables, the demo user and 25 sample products
php artisan serve             # the API is now at http://localhost:8000
```

Demo login: **admin@example.com** / **password**

Start over with a clean database at any time: `php artisan migrate:fresh --seed`

---

## 2. Folder map (only the files you will touch)

```
app/
  Http/
    Controllers/
      Auth/AuthController.php        sign in, "who am I", sign out
      Product/ProductController.php  ★ THE CRUD EXAMPLE: list, show, create, update, delete
    Middleware/
      ForceJsonResponse.php          every answer is JSON
      AddTokenFromCookie.php         reads the sign-in cookie
  Models/
    User.php
    Product/ProductModel.php         one row of the products table
  Traits/
    ResponseTrait.php                the { success: ... } / { error: ... } reply shape
    PaginatedListTrait.php           paging, search, sort and filters for tables
  Exceptions/Handler.php             friendly 401 / 404 messages
config/
  cors.php                           which website may call the API
database/
  migrations/                        the table definitions
  seeders/                           demo data
routes/
  api.php                            ★ every URL of the API
```

---

## 3. How a request works

```
React app ──► routes/api.php ──► middleware ──► Controller method ──► Model ──► MySQL
                                                       │
                       { "success": { "message": "...", "data": ... } } ◄┘
```

1. **Route** (`routes/api.php`) matches the URL to a controller method, e.g. `POST /api/products` → `ProductController@onCreate`.
2. **Middleware** runs first. `auth:api` stops anyone who isn't signed in (401).
3. **Controller** checks the input with `$request->validate([...])`. If a rule fails, Laravel answers **422** with the errors for each field, and your code below never runs.
4. **Model** reads or writes the database.
5. The answer always has the same shape (`ResponseTrait`):

```json
{ "success": { "message": "Product created.", "data": { "id": 26, "code": "PRD-0026", ... } } }
{ "error":   { "message": "Record not found." } }
```

### Sign-in

`POST /api/auth/login` checks the password and creates a Passport token. The token goes into an **HttpOnly cookie** (`auth_token`).
The browser keeps it and sends it with every request, and JavaScript can't read it, so it can't be stolen by a bad script.
`AddTokenFromCookie` turns the cookie into an `Authorization: Bearer ...` header for Passport.

Postman keeps cookies too: sign in once with `POST /api/auth/login`, and the next requests are signed in. See [docs/09-testing-the-api.md](docs/09-testing-the-api.md) for Postman and curl.

---

## 4. The endpoints

| Method | URL | What it does |
| --- | --- | --- |
| POST | `/api/auth/login` | sign in `{ email, password }` |
| GET | `/api/me` | the signed-in user |
| POST | `/api/logout` | sign out |
| GET | `/api/products/list?page=1&per_page=10&search=&sort_by=name&sort_dir=asc&filters[category][]=Cleaning` | one page of the table |
| GET | `/api/products/{id}` | one product |
| POST | `/api/products` | create |
| PUT | `/api/products/{id}` | update |
| DELETE | `/api/products/{id}` | delete |

See every route: `php artisan route:list --path=api`

---

## 5. Add your own module (example: Customers)

1. **Table:** `php artisan make:migration create_customers_table`, add the columns (copy the products migration), then `php artisan migrate`.
2. **Model:** copy `app/Models/Product/ProductModel.php` to `app/Models/Customer/CustomerModel.php`. Change the namespace, `$table`, `$fillable` and `$casts`.
3. **Controller:** copy `app/Http/Controllers/Product/ProductController.php` to `Customer/CustomerController.php`. Change the model, the columns in `onGetList`, and `rules()`.
4. **Routes:** in `routes/api.php`, copy the `products` group inside `auth:api` and rename it `customers`.
   - Keep `list` above `{customer}`.
   - The `{customer}` name must match the controller's `CustomerModel $customer` argument.
5. **Test it:** sign in with Postman (or the React app) and call `GET /api/customers/list`.

Then build the screen in the React app (see its README).

---

## 6. Useful commands

```bash
php artisan serve                  # run the API
php artisan migrate                # run new migrations
php artisan migrate:fresh --seed   # wipe the database and seed it again
php artisan route:list --path=api  # list the API's URLs
php artisan tinker                 # try PHP code against your app, e.g. App\Models\User::count()
```

## 7. Troubleshooting

- **The React app says "Unable to reach the server".** Is `php artisan serve` running? Does `VITE_API_URL` in the React `.env` match?
- **CORS error in the browser console.** `BASE_URL` in this `.env` must be the React app's exact address, e.g. `http://localhost:5173`.
  If Vite picked another port (5174, ...) because 5173 was busy, update `BASE_URL`.
- **Signed in but every request says "Please sign in."** Open both apps with `localhost`, not `127.0.0.1`. The cookie only works when both use the same host name.
- **"Personal access client not found".** Run `php artisan db:seed` again; it creates the client.
