# Validation

**What it is:** checking the input before you use it. If a rule fails, Laravel answers **422** with a message for each field, and your code after `validate()` never runs.

**Where we use it:** `rules()` in `ProductController`, and the login in `AuthController`.

Official docs (the full list of rules): https://laravel.com/docs/10.x/validation#available-validation-rules

---

## How it works

```php
public function onCreate(Request $request)
{
    $data = $request->validate([
        'code' => 'required|string|max:20|unique:products,code',
        'price' => 'required|numeric|min:0',
        'description' => 'nullable|string|max:500',
    ]);

    // $data has ONLY the fields listed above, already checked.
    $product = ProductModel::create($data);
}
```

When `code` is missing, the answer is:

```json
{ "message": "The code field is required.", "errors": { "code": ["The code field is required."] } }
```

The React app (`apiClient.ts`) turns `errors` into the toast.

## Rules you'll use most

| Rule | Means |
| --- | --- |
| `required` | must be there and not empty |
| `nullable` | may be null or empty (put it first) |
| `sometimes` | only check this field if it was sent |
| `string`, `integer`, `numeric`, `boolean`, `array`, `date` | type |
| `email` | a valid email |
| `min:0` / `max:120` | number size, or text length |
| `in:Cash,Card` | one of these values |
| `unique:products,code` | no other row has this value |
| `exists:customers,id` | the id must exist in that table (for foreign keys) |
| `confirmed` | must match `<field>_confirmation` (passwords) |
| `after:today`, `before:end_date` | dates |
| `array`, `items.*.qty` | a list, and a rule for each item's field |

Rules can be written as `'required|max:20'` or as a list `['required', 'max:20']`. Use the list when a rule is an object.

## Rule objects

```php
use Illuminate\Validation\Rule;

'category' => ['required', Rule::in(ProductModel::CATEGORIES)],

// On update, the product may keep its own code:
'code' => ['required', Rule::unique('products', 'code')->ignore($product?->id)],
```

## One `rules()` for create and update

```php
private function rules(?ProductModel $product = null): array
{
    return [
        'code' => ['required', 'string', 'max:20', Rule::unique('products', 'code')->ignore($product?->id)],
        // ...
    ];
}

$this->rules()          // create
$this->rules($product)  // update
```

## Your own messages

```php
$request->validate(
    ['code' => 'required|unique:products,code'],
    ['code.unique' => 'Another product already uses this code.']
);
```

## Checks that need the database or other logic

Validate first, then check, then answer with an error yourself:

```php
if ($product->orders()->exists()) {
    return $this->dataResponse('error', 403, "You can't delete a product that has orders.");
}
```

## Keep the two sides the same

The React form checks the same rules with zod (`src/schemas/product.schema.ts`) so users see errors right away. The API checks them again because anyone can call it directly. **When you change one, change the other.**
