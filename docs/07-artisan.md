# Artisan commands

**What it is:** Laravel's command-line helper. Run it from the API folder with `php artisan <command>`.

See every command: `php artisan list` · help for one: `php artisan help make:model`

Official docs: https://laravel.com/docs/10.x/artisan

---

## Running

```bash
php artisan serve                   # run the API at http://localhost:8000
php artisan serve --port=8001       # on another port (then update VITE_API_URL in the React .env)
```

## Database

```bash
php artisan migrate                 # run new migrations
php artisan migrate:status          # which have run
php artisan migrate:rollback        # undo the last batch
php artisan migrate:fresh --seed    # wipe everything, rebuild, seed (local only!)
php artisan db:seed                 # run the seeders
php artisan db:seed --class=ProductSeeder
```

## Making files

The `make:` commands write a starting file in the right folder. A `/` in the name puts it in a subfolder:

```bash
php artisan make:model Customer/CustomerModel          # app/Models/Customer/CustomerModel.php
php artisan make:controller Customer/CustomerController   # app/Http/Controllers/Customer/...
php artisan make:migration create_customers_table
php artisan make:seeder CustomerSeeder
php artisan make:middleware CheckSomething
```

For a module like Products, copying the Product files and renaming them is often faster than `make:`, because the copies already have the kit's patterns. Either way works.

## Looking around

```bash
php artisan route:list --path=api   # every API URL and which method handles it
php artisan about                   # versions, environment, drivers
php artisan tinker                  # run PHP against your app
```

Tinker is great for trying things:

```
> App\Models\User::count()
> App\Models\Product\ProductModel::where('stock', 0)->pluck('name')
> App\Models\User::where('email', 'admin@example.com')->first()->update(['password' => 'newpassword'])
```

Type `exit` to leave.

## When something seems "stuck"

Laravel caches config, routes and views on servers. If a change to `.env`, `config/` or `routes/` doesn't show up:

```bash
php artisan optimize:clear          # clear all the caches
```

## First-time setup

```bash
php artisan key:generate            # makes APP_KEY in .env (once per install)
php artisan passport:keys           # Passport's keys (our seeder does this for you)
```
