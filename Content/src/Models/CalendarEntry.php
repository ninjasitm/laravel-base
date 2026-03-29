<?php
namespace Nitm\Content\Models;

class CalendarEntry extends Model {
    public const FREQUENCY_DAILY = 'daily';

    public const FREQUENCY_WEEKLY = 'weekly';

    public const FREQUENCY_MONTHLY = 'monthly';

    public const FREQUENCY_YEARLY = 'yearly';

    public const INTERVAL_DAILY = 'day';

    public const INTERVAL_WEEKLY = 'week';

    public const INTERVAL_MONTHLY = 'month';

    public const INTERVAL_YEARLY = 'year';

    protected $guarded = [];
}