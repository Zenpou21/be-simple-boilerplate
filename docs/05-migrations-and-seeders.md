# Migrations and seeders

- **Migrations** are PHP files that create or change tables. They're the history of the database, kept in Git, so every computer and server ends up with the same tables.
- **Seeders** fill tables with starting or demo data.

**Where we use them:** `database/migrations/` (including `create_products_table`) and `database/seeders/` (the demo user and 25 products).

Official docs: https://laravel.com/docs/10.x/migrations · https://laravel.com/docs/10.x/seeding

---

## Make a table

```bash
php artisan make:migration create_customers_table
```

```php
public function up(): void
{
    Schema::create('customers', function (Blueprint $table) {
        $table->id();                                   // id, auto-increment
        $table->string('name', 120);
        $table->string('email')->unique();
        $table->string('phone', 30)->nullable();        // may be empty
        $table->boolean('is_active')->default(true);
        $table->timestamps();                           // created_at, updated_at
    });
}

public function down(): void                            // how to undo it
{
    Schema::dropIfExists('customers');
}
```

```bash
php artisan migrate            # run every migration that hasn't run yet
```

## Column types

| Code | MySQL |
| --- | --- |
| `$table->string('name', 120)` | VARCHAR(120) |
| `$table->text('notes')` | TEXT (long text) |
| `$table->integer('qty')` / `unsignedInteger` | INT / INT that can't be negative |
| `$table->decimal('price', 12, 2)` | money: use this, never float |
| `$table->boolean('is_active')` | TINYINT(1) |
| `$table->date('due_on')` / `dateTime('paid_at')` | DATE / DATETIME |
| `$table->json('settings')` | JSON |
| `$table->foreignId('customer_id')->constrained('customers')` | a link to another table |

Modifiers: `->nullable()`, `->default(0)`, `->unique()`, `->index()` (faster searching/sorting on that column).

### Links between tables

```php
$table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();   // delete orders with the customer
$table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();  // block deleting a customer with orders
```

## Change an existing table

**Don't edit a migration that has already run on someone else's machine or a server.** Make a new one:

```bash
php artisan make:migration add_barcode_to_products_table
```

```php
public function up(): void
{
    Schema::table('products', function (Blueprint $table) {
        $table->string('barcode', 50)->nullable()->after('code');
    });
}

public function down(): void
{
    Schema::table('products', function (Blueprint $table) {
        $table->dropColumn('barcode');
    });
}
```

Then add the column to the model's `$fillable`, the controller's `rules()`, and the React type and form.

## Undo and start over

```bash
php artisan migrate:status          # which migrations have run
php artisan migrate:rollback        # undo the last batch (runs down())
php artisan migrate:fresh --seed    # DROP every table, migrate, seed  (local only!)
```

## Seeders

```bash
php artisan make:seeder CustomerSeeder
```

```php
class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        CustomerModel::updateOrCreate(['email' => 'juan@example.com'], ['name' => 'Juan Dela Cruz']);
    }
}
```

`updateOrCreate` (find by the first array, set the second) makes a seeder safe to run twice.

Add it to `DatabaseSeeder::run()`:

```php
$this->call(ProductSeeder::class);
$this->call(CustomerSeeder::class);
```

```bash
php artisan db:seed                          # run DatabaseSeeder
php artisan db:seed --class=CustomerSeeder   # just one
```

Our `DatabaseSeeder` also sets up Passport (its keys and the personal access client), so sign-in works right after `migrate --seed`.
