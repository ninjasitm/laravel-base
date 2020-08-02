<?php

namespace Nitm\Content\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CategoryDefaultOrderScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->getQuery()->orders = [];
        $builder->orderBy('title', 'asc');
    }

     /**
      * Apply the scope to a given Eloquent query builder.
      *
      * @param \Illuminate\Database\Eloquent\Builder $builder
      * @param \Illuminate\Database\Eloquent\Model   $model
      */
     public function remove(Builder $builder, Model $model)
     {
         $builder->orderBy('title', 'asc');
     }
}
