# PHP and Composer

**PHP** is the language Laravel is written in (this kit needs 8.1 or newer). **Composer** is PHP's package manager: it downloads the libraries in `composer.json` into `vendor/`.

Official docs: https://www.php.net/manual/en/langref.php · https://getcomposer.org/doc/01-basic-usage.md

---

## The PHP you'll see in this kit

```php
<?php

namespace App\Http\Controllers\Product;      // the "folder name" of this class

use App\Models\Product\ProductModel;          // import a class from another namespace
use Illuminate\Http\Request;

class ProductController extends Controller    // a class that builds on another
{
    use ResponseTrait;                        // a trait: copy-in shared methods (dataResponse)

    public function onGetOne(ProductModel $product)   // the type of the argument
    {
        return $this->dataResponse('success', 200, 'Product found.', $product);
    }
}
```

| Syntax | Means |
| --- | --- |
| `$name` | a variable (always starts with `$`) |
| `$this->method()` | call a method on this object |
| `$product->name` | read a field of an object |
| `ProductModel::create([...])` | call a *static* method (on the class, not an object) |
| `ProductModel::CATEGORIES` | a class constant |
| `['code' => 'PRD-1', 'stock' => 3]` | an array with keys (like a JS object) |
| `['a', 'b']` | a plain list |
| `"Hello {$user->name}"` | text with a variable inside (double quotes only) |
| `'Hello ' . $name` | join text with `.` |
| `$product?->id` | `null` if `$product` is null, otherwise its id |
| `$value ?? 'default'` | a fallback when the value is null or missing |
| `fn ($x) => $x * 2` | a short function |
| `?ProductModel $product = null` | may be a ProductModel or null; null if left out |

Debug quickly with `dd($value);`. It prints the value and stops (dump and die). Remove it afterwards.

## Namespaces must match folders

Composer finds classes by their namespace (this is set in `composer.json` under `autoload`):

```
App\Models\Product\ProductModel   ->  app/Models/Product/ProductModel.php
```

So if you move or rename a file, change its `namespace` line and class name to match. Otherwise you get `Class "..." not found`.

## Composer commands

```bash
composer install                 # download everything (after cloning, or when composer.lock changed)
composer require vendor/package  # add a library
composer remove vendor/package   # remove one
composer dump-autoload           # rebuild the class list (try this after moving files)
```

- Commit `composer.json` and `composer.lock`.
- Never commit `vendor/`. It's already in `.gitignore`.
- Find packages at https://packagist.org.
