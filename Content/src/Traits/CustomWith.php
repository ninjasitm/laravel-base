<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Str;

trait CustomWith
{
    public function scopeCustomWith($query, bool $includeDefaultWith = true, $with = [])
    {
        if (!isset($this->customWith)) {
            return;
        }

        $query->with($this->buildWith($this->customWith));
        if ($includeDefaultWith) {
            $query->with($this->buildWith($this->with));
        }
        if (!empty($with)) {
            $query->with($this->buildWith($with));
        }

        return $query;
    }

    public function getWith()
    {
        return (array) $this->with;
    }

    public function getCustomWith()
    {
        if (!isset($this->customWith)) {
            return [];
        }

        return $this->buildWith($this->customWith);
    }

    public function getAllWith()
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
     * Params can only be scalar values
     * @return array
     */
    protected function buildWith($allWith)
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
                } else {
                    $result[] = is_numeric($with) ? $scopes : $with;
                }
            }
        }

        return $result;
    }

    public function addCustom($data, $property)
    {
        if (!isset($this->$property)) {
            return;
        }

        $data = is_array($data) ? $data : [$data];
        $this->$property = array_filter(is_array($this->$property) ? $this->$property : [$this->$property]);
        $relationsOnly = array_filter($data, function ($d) {
            return is_string($d);
        });
        if (!empty($relationsOnly)) {
            $this->$property = array_unique(array_merge($this->$property, $relationsOnly));
        }
        $lambdasOnly = array_filter($data, function ($d) {
            return is_callable($d);
        });
        if (!empty($lambdasOnly)) {
            $this->$property = array_merge($this->$property, $lambdasOnly);
        }
    }

    public function addCustomWith($with)
    {
        $this->addCustom($with, 'customWith');
    }

    public function addCustomWithCount($with)
    {
        $this->addCustom($with, 'customWithCount');
    }

    public function addWith($with)
    {
        $this->addCustom($with, 'with');
    }

    public function addWithCount($with)
    {
        $this->addCustom($with, 'withCount');
    }

    public function scopeCustomWithCount($query)
    {
        if (!isset($this->customWithCount)) {
            return;
        }

        return $query->withCount($this->buildWith($this->customWithCount));
    }

    public function getAllWithCount()
    {
        return array_filter(array_merge($this->buildWith((array)$this->withCount), (array)$this->getCustomWithCount()));
    }

    public function getCustomWithCount()
    {
        if (!isset($this->customWithCount)) {
            return [];
        }
        return $this->buildWith($this->customWithCount);
    }

    public function getWithCount()
    {
        return $this->buildWith($this->withCount);
    }
}