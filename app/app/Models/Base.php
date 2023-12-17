<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Base extends Model
{
    public function scopeFilter($query, Request $request, string $prefix = null, array $options = []): void
    {
        $filters = collect($request->query)->toArray();

        $tableName = $query->getModel()->getTable();

        $where = array_key_exists('where', $options) ? $options['where'] : ['name'];

        $order = array_key_exists('order', $options) ? $options['order'] : ['name'];

        $search = array_filter($filters, function ($key) use ($prefix) {
            return strpos($key, $prefix
                ? ($prefix . '_search')
                : 'search') !== false;
        }, ARRAY_FILTER_USE_KEY);
        $filterSearch = reset($search);

        $trash = array_filter($filters, function ($key) use ($prefix) {
            return strpos($key, $prefix
                ? ($prefix . '_trash')
                : 'trash') !== false;
        }, ARRAY_FILTER_USE_KEY);
        $filterTrash = reset($trash);

        $sort = array_filter($filters, function ($key) use ($prefix) {
            return strpos($key, $prefix
                ? ($prefix . '_sorted')
                : 'sorted') !== false;
        }, ARRAY_FILTER_USE_KEY);
        $filterSort = reset($sort);

        $query->when($filterSearch ?? null, function ($query, $search) use ($tableName, $where) {
            $query->where(function ($query) use ($search, $tableName, $where) {
                foreach ($where as $key => $item) {
                    if ($key == 0) {
                        $query->where("$tableName.$item", 'ilike', '%' . $search . '%');
                    } else {
                        $query->orWhere("$tableName.$item", 'ilike', '%' . $search . '%');
                    }
                }
            });
        })->when($filterTrash ?? null, function ($query, $trashed) {
            if ($trashed === 'both') {
                $query->withTrashed();
            } elseif ($trashed === 'trashed') {
                $query->onlyTrashed();
            }
        })
            ->when($filterSort ? [$filterSort] : $order, function ($query, $sortItems) {
                foreach ($sortItems as $sort) {
                    $sort_order = 'ASC';

                    if (strncmp($sort, '-', 1) === 0) {
                        $sort_order = 'DESC';
                        $sort = substr($sort, 1);
                    }

                    $query->orderBy($sort, $sort_order);
                }
            });
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }
}
