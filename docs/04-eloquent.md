# Eloquent models

**What it is:** Laravel's way to use the database with PHP objects instead of SQL. One **model** class = one table, and one model object = one row.

**Where we use it:** `app/Models/User.php` and `app/Models/Product/ProductModel.php`.

Official docs: https://laravel.com/docs/10.x/eloquent

---

## A model

```php
class ProductModel extends Model
{
    protected $table = 'products';            // the table name (needed because of the "Model" suffix)

    protected $fillable = ['code', 'name', 'price', ...];   // what create()/update() may fill

    protected $casts = [                      // database text -> real PHP types in the JSON
        'price' => 'float',
        'is_active' => 'boolean',
    ];
}
```

- **`$table`:** Laravel guesses the table name from the class name. `ProductModel` would become `product_models`, so we always set `$table`.
- **`$fillable`:** only these columns can be filled from a request. This protects columns like `id` or `is_admin` from being set by a crafted request.
- **`$casts`:** without them, MySQL decimals and booleans arrive in JSON as `"12.50"` and `1`.

## Reading

```php
ProductModel::all();                                  // every row (avoid on big tables)
ProductModel::find(12);                               // one row by id, or null
ProductModel::findOrFail(12);                         // one row, or a 404
ProductModel::where('code', 'PRD-0001')->first();     // the first match, or null

ProductModel::where('is_active', true)
    ->where('stock', '>', 0)
    ->whereIn('category', ['Electronics', 'Furniture'])
    ->orderBy('name')
    ->get();                                          // a list of matches

ProductModel::where('category', 'Cleaning')->count();
ProductModel::where('name', 'like', "%{$search}%")->get();
ProductModel::query()->paginate(10);                  // one page (PaginatedListTrait does this for tables)
```

The query is built step by step and only runs at the end (`get`, `first`, `count`, `paginate`).

## Writing

```php
$product = ProductModel::create($data);               // INSERT (only $fillable fields)

$product->update(['stock' => 10]);                    // UPDATE
$product->stock = 10;  $product->save();              // the same, field by field

$product->delete();                                   // DELETE

ProductModel::where('stock', 0)->update(['is_active' => false]);   // many rows at once
```

`created_at` and `updated_at` are filled in by Laravel.

## Relationships (when you add a second module)

Example: a customer has many orders, and each order belongs to one customer (`orders.customer_id`).

```php
// app/Models/Customer/CustomerModel.php
public function orders()
{
    return $this->hasMany(OrderModel::class, 'customer_id');
}

// app/Models/Order/OrderModel.php
public function customer()
{
    return $this->belongsTo(CustomerModel::class, 'customer_id');
}
```

> Always write the column name (`'customer_id'`) with our `XxxModel` names. Otherwise Laravel guesses `customer_model_id`.

Using them:

```php
$customer->orders;                                    // a list of the customer's orders
$order->customer->name;                               // the order's customer

OrderModel::with('customer')->get();                  // load the customers in ONE extra query
```

Use `with()` whenever you show a relation in a list. Without it, a table of 50 orders runs 50 extra queries for the customer names (the "N+1 problem").

## Trying queries without writing a page

```bash
php artisan tinker
> App\Models\Product\ProductModel::where('stock', 0)->count()
> App\Models\Product\ProductModel::first()->toArray()
```
