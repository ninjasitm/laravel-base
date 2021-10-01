<?php

namespace Nitm\Content\Database\Eloquent;

use Nitm\Content\Traits\SetUuid;

/**
 * Custom Eloquent Builder
 */
class UuidBuilder extends Builder
{
    /**
     * Find a model by its primary key.
     *
     * @param  mixed $id
     * @param  array $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function find($id, $columns = ['*'])
    {
        $query = $this->newQuery();
        if (is_array($id) || $id instanceof Arrayable) {
            return $query->findMany($id, $columns);
        }

        if (
            is_string($id)
            && !is_numeric($id)
            && in_array(SetUuid::class, class_uses(get_class($query->getModel())))
            && property_exists($query->getModel(), 'uuidFields')
            && !empty($query->getModel()->uuidFields)
        ) {
            foreach ($query->getModel()->uuidFields as $field) {
                $query->orWhere($field, $id);
            }
            return $query->first();
        }

        return $query->whereKey($id)->first($columns);
    }

    /**
     * Find multiple models by their primary keys.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $ids
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findMany($ids, $columns = ['*'])
    {
        $ids = $ids instanceof Arrayable ? $ids->toArray() : $ids;

        if (empty($ids)) {
            return $this->model->newCollection();
        }

        $uuidIds = collect([]);
        $numericIds = collect([]);

        foreach ($ids as $itemId) {
            if (
                is_string($itemId)
                && !is_numeric($itemId)
            ) {
                $uuidIds->push($itemId);
            } else {
                $numericIds->push($itemId);
            }
        }

        if ($uuidIds->count()) {
            $query = $this->newQuery();
            if ($numericIds->count()) {
                $this->whereKey($numericIds->toArray());
            }
            if (
                in_array(SetUuid::class, class_uses(get_class($query->getModel())))
                && property_exists($query->getModel(), 'uuidFields')
                && !empty($query->getModel()->uuidFields)
            ) {
                foreach ($query->getModel()->uuidFields as $field) {
                    $query->orWhereIn($field, $uuidIds);
                }
            }
            return $query->get($columns);
        } else {
            return $this->whereKey($numericIds->toArray())->get($columns);
        }
    }
}