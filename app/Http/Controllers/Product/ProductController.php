<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\ProductModel;
use App\Traits\PaginatedListTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * THE CRUD EXAMPLE. Copy this file to start a new module.
 *
 *   GET    /api/products/list    one page of the table   onGetList
 *   GET    /api/products/{id}    one product             onGetOne
 *   POST   /api/products         create                  onCreate
 *   PUT    /api/products/{id}    update                  onUpdate
 *   DELETE /api/products/{id}    delete                  onDelete
 *
 * The routes are in routes/api.php. `ProductModel $product` in a method means Laravel finds the
 * product from the {product} in the URL by itself (route model binding), or answers 404.
 */
class ProductController extends Controller
{
    use ResponseTrait, PaginatedListTrait;

    public function onGetList(Request $request)
    {
        // The keys are the frontend's column uids (ProductsPage.tsx).
        $list = $this->paginatedList($request, ProductModel::query(), [
            'code' => ['column' => 'code', 'search' => true, 'sort' => true],
            'name' => ['column' => 'name', 'search' => true, 'sort' => true],
            'category' => ['column' => 'category', 'search' => true, 'sort' => true, 'filter' => true],
            'price' => ['column' => 'price', 'sort' => true],
            'stock' => ['column' => 'stock', 'sort' => true],
            'is_active' => ['column' => 'is_active', 'sort' => true, 'filter' => true],
        ]);

        return $this->dataResponse('success', 200, 'Products found.', $list);
    }

    public function onGetOne(ProductModel $product)
    {
        return $this->dataResponse('success', 200, 'Product found.', $product);
    }

    public function onCreate(Request $request)
    {
        // validate() answers 422 with the field errors when a rule fails; the code below only
        // runs with valid data, and only the fields in rules() are kept.
        $product = ProductModel::create($request->validate($this->rules()));

        return $this->dataResponse('success', 201, 'Product created.', $product);
    }

    public function onUpdate(Request $request, ProductModel $product)
    {
        $product->update($request->validate($this->rules($product)));

        return $this->dataResponse('success', 200, 'Product updated.', $product);
    }

    public function onDelete(ProductModel $product)
    {
        $product->delete();

        return $this->dataResponse('success', 200, 'Product deleted.');
    }

    /** The same rules for create and update. On update, the product may keep its own code. */
    private function rules(?ProductModel $product = null): array
    {
        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('products', 'code')->ignore($product?->id)],
            'name' => 'required|string|max:120',
            'category' => ['required', Rule::in(ProductModel::CATEGORIES)],
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];
    }
}
