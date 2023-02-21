<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Nitm\Helpers\ClassHelper;
use Vinkla\Hashids\Facades\Hashids;

/**
 * Support hashids for items that are arrayable
 */
trait SupportsHashIds
{
    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function getHashIdAttribute()
    {
        return Hashids::encode($this->id);
    }

    /**
     * Resolve Route Binding using hashids
     *
     * @param  mixed $value
     * @param  mixed $field
     * @return void
     */
    public function resolveRouteBindingUsingHashId($value, $field = null)
    {
        if (is_numeric($value)) {
            return static::findOrFail($value);
        }
        return ClassHelper::hasTrait($this->resource, 'Nitm\Content\Traits\SupportsHashIds') ? static::where($field ?: 'id', Hashids::decode($value))->firstOrFail() : static::where($field ?: 'uuid', $value)->firstOrFail();
    }
}
