<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Str;
use Nitm\Content\Traits\SetUuid;

trait SetUuid
{
    public static function bootSetUuid()
    {
        static::creating(
            function ($model) {
                if (!property_exists($model, 'uuidFields')) {
                    return;
                }
                if (!isset($model->uuidFields)) {
                    return;
                }

                foreach ((array)$model->uuidFields as $field) {
                    $model->$field = $model->$field ?? Str::uuid();
                }
            }
        );
    }

    /**
     * Find a model by its primary key.
     *
     * @param  mixed $id
     * @param  array $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public static function find($id, $columns = ['*'])
    {
        $query = new static;
        if (is_array($id) || $id instanceof Arrayable) {
            return $query->findMany($id, $columns);
        }

        if(is_string($id)
            && !is_numeric($id)
            && in_array(SetUuid::class, class_uses(get_class($query->getModel())))
            && property_exists($query->getModel(), 'uuidFields')
            && !empty($query->getModel()->uuidFields)
        ) {
            foreach($query->getModel()->uuidFields as $field) {
                $query->orWhere($field, $id);
            }
            return $query->first();
        }

        return $query->whereKey($id)->first($columns);
    }
}