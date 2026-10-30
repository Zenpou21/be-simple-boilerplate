<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Paging, search, sorting and filters for table list endpoints (the React DataTable).
 *
 * The frontend sends:  ?page=1&per_page=10&search=pen&sort_by=name&sort_dir=asc&filters[category][]=Office
 * This returns:        ['items' => [...one page of rows...], 'meta' => [total, page, per_page, last_page]]
 *
 * Usage in a controller:
 *
 *   $list = $this->paginatedList($request, ProductModel::query(), [
 *       'code'     => ['column' => 'code', 'search' => true, 'sort' => true],
 *       'category' => ['column' => 'category', 'sort' => true, 'filter' => true],
 *   ]);
 *
 * The array keys ("code", "category") are the names the frontend uses. Only the columns listed
 * here can be searched, sorted or filtered, so a user can never sort by a column you didn't allow.
 */
trait PaginatedListTrait
{
    protected function paginatedList(Request $request, Builder $query, array $columns): array
    {
        $allowed = fn (string $option) => array_keys(array_filter($columns, fn ($config) => !empty($config[$option])));

        // Check the query string first: anything unexpected is answered with a 422 error.
        $params = $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => ['sometimes', 'integer', Rule::in([10, 20, 50, 100])],
            'search' => 'sometimes|nullable|string|max:100',
            'sort_by' => ['sometimes', 'string', Rule::in(['id', ...$allowed('sort')])],
            'sort_dir' => 'sometimes|in:asc,desc',
            'filters' => 'sometimes|array',
            'filters.*' => 'array',
        ]);

        // Search: a row matches when ANY searchable column contains the text.
        $search = trim($params['search'] ?? '');
        if ($search !== '') {
            $query->where(function (Builder $where) use ($columns, $search, $allowed) {
                foreach ($allowed('search') as $uid) {
                    $where->orWhere($columns[$uid]['column'], 'like', "%{$search}%");
                }
            });
        }

        // Filters: e.g. filters[category][]=Office keeps only Office products.
        foreach ($params['filters'] ?? [] as $uid => $values) {
            if (in_array($uid, $allowed('filter'), true) && $values !== []) {
                $query->whereIn($columns[$uid]['column'], $values);
            }
        }

        // Sorting: always ends with id, so rows never jump between pages.
        $sortBy = $params['sort_by'] ?? 'id';
        $direction = $params['sort_dir'] ?? 'desc';
        if ($sortBy !== 'id') {
            $query->orderBy($columns[$sortBy]['column'], $direction);
        }
        $query->orderBy('id', $sortBy === 'id' ? $direction : 'asc');

        $page = $query->paginate((int) ($params['per_page'] ?? 10), ['*'], 'page', (int) ($params['page'] ?? 1));

        return [
            'items' => $page->items(),
            'meta' => [
                'total' => $page->total(),
                'page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'last_page' => $page->lastPage(),
            ],
        ];
    }
}
