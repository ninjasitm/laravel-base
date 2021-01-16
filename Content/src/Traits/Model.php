<?php

namespace Nitm\Content\Traits;

use Nitm\Content\Models\User;
use Nitm\Content\Models\Team;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Traits for Model.
 */
trait Model
{
    public static $tableColumns = [];

    /**
     * Toggle a single atribute on the model
     *
     * @param  string|array $attributes The atributes to be toggled
     * @return array The toggled attributes
     */
    public function toggle($attributes = 'is_active')
    {
        $allAttributes = is_array($attributes) ? $attributes : (array)$attributes;
        foreach ($allAttributes as $attribute) {
            $newValue = $this->$attribute === true ? false : true;
            $this->$attribute = $newValue;
        }
        $this->save();
        return Arr::only($this->toArray(), array_merge(['id'], $allAttributes));
    }

    /**
     * Get stats for this model
     *
     * @param array                     $stats
     * @param \Nitm\Content\Models\Team $team
     * @param array                     $groups
     * @param \Nitm\Content\Models\User $user
     *
     * @return void
     */
    public static function getStats(array $stats, Team $team, $groups = [], User $user = null)
    {
        $user = $user ?: auth()->user();
        $snakeable = array_merge(array_keys($team->featureNames), ['mentor', 'student', 'mentors', 'students']);
        if ($user->isMentorOn($team)) {
            $stats = Arr::only($stats, Arr::get($groups, 'mentor', array_keys($stats)));
        } elseif ($user->isStudentOn($team)) {
            $stats = Arr::only($stats, Arr::get($groups, 'student', array_keys($stats)));
        }
        $availableStats = [];
        foreach ($stats as $key => $stat) {
            if (in_array($stat, $snakeable)) {
                $availableStats[$team->snakeFeatureName($key, false, false)] = $stat;
            } else {
                $availableStats[$key] = $stat;
            }
        }
        return $availableStats;
    }

    /**
     * Convert a model to a repository model
     *
     * @return Model
     */
    public function toRepository()
    {
        $class = get_class($this);
        $repositoryClass = str_replace('Nitm\Content\\Models', 'Nitm\Content\\Models\\Repositories', $class);
        if (!class_exists($repositoryClass)) {
            $repositoryClass = 'Nitm\Content\\Models\\Repositories\\' . class_basename($class);
        }
        if (!class_exists($repositoryClass)) {
            $repositoryClass = str_replace('Nitm\Content\\Models', 'Nitm\Content\\Repositories', class_basename($class));
        }

        if (!class_exists($repositoryClass)) {
            throw new \Exception("$repositoryClass doesn't exist!");
        }
        $model = new $repositoryClass($this->getAttributes());
        $model->id = $this->id;
        $model->exists = $this->exists;
        if (is_array($this->_relations) && !empty($this->_relations)) {
            foreach ($this->_relations as $relation => $value) {
                $model->setRelation($relation, $value);
            }
        }
        return $model;
    }

    /**
     * Get the fillable fields for this model
     *
     * @param \Nitm\Content\Models\User $user
     *
     * @return array
     */
    public function getFillableForUser(User $user = null): array
    {
        $user = $user ?? auth()->user();
        return array_unique(array_merge($this->fillable, $this->getAllWith()));
    }

    public static function getMacros()
    {
        $class = static::class;
        return $class::$macros;
    }

    /**
     * Add fillable attributes for the model.
     *
     * @param  array|string|null $attributes
     * @return void
     */
    public function addFillable($attributes = null)
    {
        $attributes = is_array($attributes) ? $attributes : func_get_args();

        $this->fillable = array_merge($this->fillable, $attributes);
    }
    /**
     * Add fillable attributes for the model.
     *
     * @param  array|string|null $attributes
     * @return void
     */
    public function addAppends($attributes = null)
    {
        $attributes = is_array($attributes) ? $attributes : func_get_args();

        $this->appends = array_merge($this->appends, $attributes);
    }

    /**
     * Add jsonable attributes for the model.
     *
     * @param  array|string|null $attributes
     * @return void
     */
    public function addJsonable($attributes = null)
    {
        $attributes = is_array($attributes) ? $attributes : func_get_args();

        $this->jsonable = array_merge($this->jsonable, $attributes);
    }

    /**
     * Get the table columns for a particular model.
     *
     * @method getTableColumns
     *
     * @return [type] [description]
     */
    public function getTableColumns($table = null)
    {
        $table = $table ?: $this->getTable();
        if (!isset(static::$tableColumns[$table])) {
            $manager = $this->getConnection()->getDoctrineSchemaManager();
            static::$tableColumns[$table] = $manager->listTableColumns($table);
        }

        return static::$tableColumns[$table];
    }

    /**
     * Does the specified columnet exist?
     *
     * @param string $column
     *
     * @return boolean
     */
    public function hasColumn(string $column): bool
    {
        return array_key_exists($column, $this->getTableColumns());
    }

    /**
     * Undocumented function
     *
     * @param  array $options
     * @return void
     */
    public static function getFilterOptions($options = [])
    {
        return static::getFormOptions($options);
    }

    /**
     * Undocumented function
     *
     * @param  array $options
     * @return void
     */
    public static function getFormOptions($options = [])
    {
        $data = [];
        $modelClass = static::class;
        $options = Arr::get($options, 'all', ['form', 'filter', 'status', 'day_of_week'], $options);
        if (in_array('status', $options) && method_exists($modelClass, 'getStatusOptions')) {
            $data['status'] = $modelClass::getStatusOptions();
        }
        if (in_array('day_of_week', $options) && method_exists($modelClass, 'getDayOfWeekOptions')) {
            $data['day_of_week'] = $modelClass::getDayOfWeekOptions();
        }
        if (in_array('priority', $options) && method_exists($modelClass, 'getPriorityOptions')) {
            $data['priority'] = $modelClass::gePriorityOptions();
        }
        return $data;
    }

    /**
     * Get a nested array for select element.
     *
     * @param string $getter   The method to use to retrieve the data
     * @param string $labelKey THe key to use as the label value
     *
     * @return array The nested options
     */
    public function getDropdownOptions($getter, $valueKey = 'title', $labelKey = 'id')
    {
        return $this->$getter(true)->lists($valueKey, $labelKey);
    }

    /**
     * Set a given attribute on the model directly without mutators.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttributeDirectly($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Undocumented function
     *
     * @param  [type] $value
     * @return void
     */
    public function setIsActiveAttribute($value = null)
    {
        $this->attributes['is_active'] = \Nitm\Helpers\ModelHelper::boolval($value);
    }

    /**
     * Undocumented function
     *
     * @param  [type] $value
     * @return void
     */
    public function setIsPublicAttribute($value = null)
    {
        $this->attributes['is_public'] = \Nitm\Helpers\ModelHelper::boolval($value);
    }

    /**
     * Undocumented function
     *
     * @param  [type] $value
     * @return void
     */
    public function setIsPrivateAttribute($value = null)
    {
        $this->attributes['is_private'] = \Nitm\Helpers\ModelHelper::boolval($value);
    }

    /**
     * Undocumented function
     *
     * @param  [type] $value
     * @return void
     */
    public function setIsRecurringAttribute($value = null)
    {
        $this->attributes['is_recurring'] = \Nitm\Helpers\ModelHelper::boolval($value);
    }

    /**
     * Undocumented function
     *
     * @param  [type] $value
     * @return void
     */
    public function setIsRequiredAttribute($value = null)
    {
        $this->attributes['is_required'] = \Nitm\Helpers\ModelHelper::boolval($value);
    }

    /**
     * Undocumented function
     *
     * @param  string $date
     * @param  string $format
     * @return Carbon || null
     */
    protected function parseDate($date, $format = 'Y-m-d H:i:s')
    {
        try {
            $date = Carbon::parse($date) ? $date : null;
            return $date ? (new Carbon($date))->format($format) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * -------------------
     * Scopes
     * -------------------
     */

    /**
     * Only get items that are active
     *
     * @param  [type] $query
     * @return void
     */
    public function scopeIsActive($query)
    {
        $query->where(['is_active' => true]);
    }

    /**
     * Save a relation
     *
     * @param  [type] $relation
     * @param  [type] $value
     * @param  string $method
     * @return void
     */
    public function saveRelation($relation, $value, $method = 'save')
    {
        $relation = Str::camel($relation);
        if ($this->exists) {
            $model = $this->$relation()->$method($value);
            $this->setRelation($relation, $model);
        } else {
            static::saved(
                function () use ($relation, $value, $method) {
                    $model = $this->$relation()->$method($value);
                    $this->setRelation($relation, $model);
                }
            );
        }
    }

    /**
     * @param array $relations
     * @param array $attributes
     *
     * @return Model
     */
    public function replicateUsing($relations = [], $attributes = []): EloquentModel
    {
        $model = $this->replicate();
        $model->id = $this->id;
        $model->exists = true;
        if (is_array($relations) && !empty($this->relations)) {
            $model->setRelations([]);
            $model->setRelations(!empty($relations) ? Arr::only($this->relations, $relations) : $this->relations);
        }

        return $model;
    }
}