<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Str;

trait CustomWith
{
    /**
     * Scope Custom With
     *
     * @param mixed $query
     * @param mixed $includeDefaultWith
     * @param mixed $with
     *
     * @return Builder
     */
    public function scopeCustomWith($query, bool $includeDefaultWith = true, $with = [])
    {
        if (!isset($this->customWith)) {
            return $query;
        }

        $query->with($this->getCustomWith());
        if ($includeDefaultWith) {
            $query->with($this->buildWith($this->with));
        }
        if (!empty($with)) {
            $query->with($this->buildWith($with));
        }

        return $query;
    }

    /**
     * Get With
     *
     * @return array
     */
    public function getWith(): array
    {
        return (array) $this->with;
    }

    /**
     * Get Custom With
     *
     * @return array
     */
    public function getCustomWith(): array
    {
        if (!isset($this->customWith)) {
            return [];
        }

        return $this->buildWith($this->customWith);
    }

    /**
     * Get All With
     *
     * @return array
     */
    public function getAllWith(): array
    {
        return array_filter(array_merge($this->buildWith((array)$this->with), (array)$this->getCustomWith()));
    }

    /**
     * Build the with conditions
     * User's can specify scopes for relations as in:
     * [
     *  relation => scope1:param1,param2|scope2|scope3:param1,param2,
     *  relation,
     *  relation
     * ]
     *
     * @param mixed $allWith Params can only be scalar values
     *
     * @return array
     */
    protected function buildWith($allWith): array
    {
        $result = [];
        if (is_array($allWith) && !empty($allWith)) {
            foreach ($allWith as $with => $scopes) {
                if (is_string($scopes) && is_string($with)) {
                    $scopes = explode('|', $scopes);
                    foreach ($scopes as $params) {
                        $parts = explode(':', $params);
                        $scope = Str::studly(array_shift($parts));
                        $result[Str::camel($scope)] = function ($query) use ($scope, $parts) {
                            if (method_exists($query->getModel(), 'scope' . $scope)) {
                                $parts = !is_string($parts) ? explode(',', $parts) : [];
                                $scope = Str::camel($scope);
                                $query->$scope(...$parts);
                            }
                        };
                    }
                } else if (is_string($with)) {
                    $result[$with] = $scopes;
                } else {
                    $result[] = is_numeric($with) ? $scopes : $with;
                }
            }
        }

        return $result;
    }

    /**
     * Add Custom
     *
     * @param mixed $data
     * @param mixed $property
     *
     * @return void
     */
    public function addCustom($data, $property)
    {
        if (!isset($this->$property)) {
            return;
        }

        $data = is_array($data) ? $data : [$data];
        $this->$property = array_filter(is_array($this->$property) ? $this->$property : [$this->$property]);
        $relationsOnly = array_filter(
            $data,
            function ($d) {
                return is_string($d);
            }
        );
        if (!empty($relationsOnly)) {
            $this->$property = array_unique(array_merge($this->$property, $relationsOnly));
        }
        $lambdasOnly = array_filter(
            $data,
            function ($d) {
                return is_callable($d);
            }
        );
        if (!empty($lambdasOnly)) {
            $this->$property = array_merge($this->$property, $lambdasOnly);
        }
    }

    /**
     * Set Eager Counts
     *
     * @param  mixed $counts
     * @return void
     */
    public function setEagerLoadsCounts(array $counts)
    {
        $this->withCount = $counts;
        return $this;
    }

    /**
     * Set Eager Counts
     *
     * @param  mixed $counts
     * @return void
     */
    public function scopeSetEagerCounts($query, array $counts)
    {
        $query->withCount = [];
        $query->getModel()->setEagerLoadsCounts($counts);
        return $query;
    }

    /**
     * Add Custom With
     *
     * @param mixed $with
     *
     * @return void
     */
    public function addCustomWith($with)
    {
        $this->addCustom($with, 'customWith');
    }

    /**
     * Add Custom With Count
     *
     * @param mixed $with
     *
     * @return void
     */
    public function addCustomWithCount($with)
    {
        $this->addCustom($with, 'customWithCount');
    }

    /**
     * Add With
     *
     * @param mixed $with
     *
     * @return void
     */
    public function addWith($with)
    {
        $this->addCustom($with, 'with');
    }

    /**
     * Add With Count
     *
     * @param mixed $with
     *
     * @return void
     */
    public function addWithCount($with)
    {
        $this->addCustom($with, 'withCount');
    }

    /**
     * Scope Custom With Count
     *
     * @param mixed $query
     * @param array $withCount
     *
     * @return void
     */
    public function scopeCustomWithCount($query, array $withCount = [])
    {
        if (!isset($this->customWithCount)) {
            return;
        }

        return $query->withCount($this->buildWith(array_merge($this->customWithCount, $withCount)));
    }

    /**
     * Get All With Count
     *
     * @return void
     */
    public function getAllWithCount()
    {
        return array_filter(array_merge($this->buildWith((array)$this->withCount), (array)$this->getCustomWithCount()));
    }

    /**
     * Get Custom With Count
     *
     * @return void
     */
    public function getCustomWithCount()
    {
        if (!isset($this->customWithCount)) {
            return [];
        }
        return $this->buildWith($this->customWithCount);
    }

    /**
     * Get With Count
     *
     * @return void
     */
    public function getWithCount()
    {
        return $this->buildWith($this->withCount);
    }
}