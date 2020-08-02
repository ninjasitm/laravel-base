<?php

namespace Nitm\Content\Behaviors;

class Activity extends \October\Rain\Extension\ExtensionBase
{
    public $owner;
    protected static $columns;

    public $activityFields = ['updated_at', 'created_at', 'deleted_at'];

    public function __construct($owner)
    {
        if (!$owner) {
            throw new \Exception('An owner is needed for this behavior');
        }
        $this->owner = $owner;
        if (!isset(static::$columns)) {
            static::$columns = \DB::getSchemaBuilder()->getColumnListing($owner->getTable());
        }
    }

    public function hasNew($timestamp)
    {
        $columns = array_intersect($this->activityFields, static::$columns);
        $query = $this->owner->newQuery();
        $activity = $query->selectRaw('GREATEST('.implode(',', $columns).') as lastActivity')->first();

        return strtotime($timestamp) < strtotime($activity->lastactivity);
    }
}
