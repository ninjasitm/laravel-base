<?php
namespace Nitm\Content\Models;

class Calendar extends Model {
    public const DEFAULT_START_TIME = '08:00:00';

    public const DEFAULT_END_TIME = '17:00:00';

    public const STATUS_ON = 'on';

    protected $guarded = [];
}