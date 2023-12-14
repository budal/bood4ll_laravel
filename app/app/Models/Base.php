<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Base extends Model
{
    public function scopeFilter($query, Request $request, string $prefix = null, string $orderBy = 'name'): void
    {
        $filters = collect($request->query)->toArray();

        $search = array_filter($filters, function ($key) use ($prefix) {
            return strpos($key, $prefix
                ? ($prefix.'_search')
                : 'search') !== false
            ;
        }, ARRAY_FILTER_USE_KEY);
        $filterSearch = reset($search);

        $trash = array_filter($filters, function ($key) use ($prefix) {
            return strpos($key, $prefix
                ? ($prefix.'_trash')
                : 'trash') !== false
            ;
        }, ARRAY_FILTER_USE_KEY);
        $filterTrash = reset($trash);

        $sort = array_filter($filters, function ($key) use ($prefix) {
            return strpos($key, $prefix
                ? ($prefix.'_sorted')
                : 'sorted') !== false
            ;
        }, ARRAY_FILTER_USE_KEY);
        $filterSort = reset($sort);

        $tableName = $query->getModel()->getTable();

        $orderByItems = explode(',', $orderBy);

        $query->when($filterSearch ?? null, function ($query, $search) use ($tableName, $orderByItems) {
            $query->where(function ($query) use ($search, $tableName, $orderByItems) {
                foreach ($orderByItems as $key => $item) {
                    if ($key == 0) {
                        $query->where("$tableName.$item", 'ilike', '%'.$search.'%');
                    } else {
                        $query->orWhere("$tableName.$item", 'ilike', '%'.$search.'%');
                    }
                }
            });
        })->when($filterTrash ?? null, function ($query, $trashed) {
            if ($trashed === 'both') {
                $query->withTrashed();
            } elseif ($trashed === 'trashed') {
                $query->onlyTrashed();
            }
        })->when($filterSort ? $filterSort : $orderBy, function ($query, $sort) {
            $sort_order = 'ASC';

            if (strncmp($sort, '-', 1) === 0) {
                $sort_order = 'DESC';
                $sort = substr($sort, 1);
            }

            $sortItems = explode(',', $sort);

            foreach ($sortItems as $sortItem) {
                $query->orderBy($sortItem, $sort_order);
            }
        });
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }
}
