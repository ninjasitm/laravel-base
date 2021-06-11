<?php

namespace Nitm\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * This class provides configuration helper functions for config variables.
 *
 * @author malcolm@ninjasitm.com
 */
class CollectionHelper
{
    /**
     * Get pagination for the collection
     */
    public static function getPagination($collection)
    {
        if (
            !$collection || $collection && !$collection instanceof \Illuminate\Pagination\LengthAwarePaginator
            || $collection instanceof \Illuminate\Pagination\Paginator
        ) {
            return [];
        }
        $last = $collection->lastPage();
        if ($collection->lastPage() == 0) {
            $next = $last = $previous = $current = 1;
        } elseif ($collection->currentPage() == $collection->lastPage()) {
            $next = $last = $collection->lastPage();
            $previous = $collection->currentPage() == 1 ? 1 : $collection->currentPage() - 1;
        } else {
            $next = $collection->currentPage() + 1;
            $previous = $collection->currentPage() - 1;
        }

        return [
            'count' => $collection->count(),
            'total' => $collection->total(),
            'last' => $last,
            'next' => $next,
            'previous' => $previous,
            'current' => $collection->currentPage(),
        ];
    }

    /**
     * @param mixed $item
     *
     * @return bool
     */
    public static function isCollection($item): bool
    {
        return $item instanceof Collection || $item instanceof EloquentCollection;
    }
}