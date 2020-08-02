<?php namespace Nitm\Api\Models;

use Model;
use Request;
use DbDongle;


class Logs extends Model
{
    protected $table = 'nitm_api_logs';

    protected $guarded = [];

    protected $jsonable = ['referer'];

    /**
     * Creates a log record
     * @return self
     */
    public static function add()
    {
        if (!DbDongle::hasDatabase())
            return self::class;

        $record = static::firstOrNew([
            'fullurl' => Request::fullUrl()
        ]);

        if ($referer = Request::header('referer')) {
            $referers = (array) $record->referer ?: [];
            $referers[] = $referer;
            $record->referer = $referers;
        }

        if (!$record->exists)
            $record->save();

        return $record;
    }
}
