<?php

namespace Nitm\Content\Database\Eloquent;

use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Nitm\Content\Database\Eloquent\Builder as NitmBuilder;
use Str;

/**
 * Custom Eloquent Builder
 */
class Builder extends BaseBuilder
{
    protected static $eagerLoads = [];

    public static function appendEagerLoad($with)
    {
        $with = (array) $with;
        if (!empty($with)) {
            foreach ($with as $name => $value) {
                if (is_numeric($name)) {
                    array_push(NitmBuilder::$eagerLoads, $value);
                    NitmBuilder::$eagerLoads = array_unique(NitmBuilder::$eagerLoads);
                } else {
                    NitmBuilder::$eagerLoads[$name] = $value;
                }
            }
        }
    }

    protected function parseAppendedEagerRelations()
    {
        NitmBuilder::$eagerLoads = $this->parseWithRelations(NitmBuilder::$eagerLoads);

        return NitmBuilder::$eagerLoads;
    }

    /**
     * {@inheritdoc}
     */
    public function eagerLoadRelations(array $models)
    {
        $eagerLoad = static::parseAppendedEagerRelations();
        $eagerLoad = array_merge($eagerLoad, $this->eagerLoad);
        if (count($models)) {
            foreach ($eagerLoad as $name => $constraints) {
                //  echo "\t".__LINE__." Attempting to eager load $name\n";
                // For nested eager loads we'll skip loading them here and they will be set as an
                // eager load on the query to retrieve the relation so that they will be eager
                // loaded on that query, because that is where they get hydrated as models.
                if (strpos($name, '.') === false && strlen($name) && is_array($models)) {
                    try {
                        $models = $this->eagerLoadRelation($models, $name, $constraints);
                    } catch (\Exception $e) {
                        \Log::error($e);
                    }
                }
            }
        }
        NitmBuilder::$eagerLoads = [];

        return (array) $models;
    }

    /**
     * Eagerly load the relationship on a set of models.
     *
     * @param array    $models
     * @param string   $name
     * @param \Closure $constraints
     *
     * @return array
     */
    protected function eagerLoadRelation(array $models, $name, \Closure $constraints)
    {
        // First we will "back up" the existing where conditions on the query so we can
        // add our eager constraints. Then we will merge the wheres that were on the
        // query back to it in order that any where conditions might be specified.
        try {
            $relation = $this->getRelation($name);
            $relation->addEagerConstraints($models);
            $constraints($relation);
            // Once we have the results, we just match those back up to their parent models
            // using the relationship instance. Then we just return the finished arrays
            // of models which have been eagerly hydrated and are readied for return.
            $models = $relation->match(
                $relation->initRelation($models, $name),
                $relation->getEager(),
                $name
            );
            //  echo "Eager loaded $name for ".get_class($this->getModel())."\n";
            //  print_r($models);

            return $models;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    /**
     * Parse a list of relations into individuals.
     *
     * @param array $relations
     *
     * @return array
     */
    protected function parseWithRelations(array $relations)
    {
        $results = [];
        foreach ($relations as $name => $constraints) {
            // If the "relation" value is actually a numeric key, we can assume that no
            // constraints have been specified for the eager load and we'll just put
            // an empty Closure with the loader so that we can treat all the same.
            if (is_numeric($name)) {
                $name = $constraints;
                list($name, $constraints) = Str::contains($name, ':')
                    ? $this->createSelectWithConstraint($name)
                    : [$name, function () {
                    }];
            }
            // We need to separate out any nested includes. Which allows the developers
            // to load deep relationships using "dots" without stating each level of
            // the relationship with its own key in the array of eager load names.
            $results = $this->addNestedWiths($name, $results);
            $results[$name] = $constraints;
        }

        return $results;
    }

    /**
     * Parse the nested relationships in a relation.
     *
     * @param string $name
     * @param array  $results
     *
     * @return array
     */
    protected function addNestedWiths($name, $results)
    {
        $progress = [];
        // If the relation has already been set on the result array, we will not set it
        // again, since that would override any constraints that were already placed
        // on the relationships. We will only set the ones that are not specified.
        foreach (explode('.', $name) as $segment) {
            $progress[] = $segment;
            if (!isset($results[$last = implode('.', $progress)])) {
                $results[$last] = function () {
                };
            }
        }

        return $results;
    }
}