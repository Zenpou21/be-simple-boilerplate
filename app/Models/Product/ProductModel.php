<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

/**
 * One row of the `products` table (see the create_products_table migration).
 */
class ProductModel extends Model
{
    /** The allowed categories. Keep the same list in the React app (constants/product.ts). */
    public const CATEGORIES = ['Electronics', 'Furniture', 'Office Supplies', 'Food & Beverage', 'Cleaning'];

    protected $table = 'products';

    /** The columns create() and update() may fill from a request. */
    protected $fillable = ['code', 'name', 'category', 'price', 'stock', 'description', 'is_active'];

    /** Turn database values into the right PHP types (so JSON has 12.5 and true, not "12.50" and 1). */
    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];
}
