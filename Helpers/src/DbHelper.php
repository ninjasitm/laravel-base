<?php

namespace Nitm\Helpers;

use Illuminate\Support\Str;
/*
 * This is the DB helper class and attempts to fill in the gap to allow smoother DB abstraction.
 * @author malcolm@ninjasitm.com
 */

use Config;
use DB;
use Cache;

class DbHelper
{
    protected static $_origFetchMode;

    protected static function beforeQuery()
    {
        static::$_origFetchMode = DB::getFetchMode();
        DB::setFetchMode(\PDO::FETCH_ASSOC);
    }

    protected static function afterQuery()
    {
        DB::setFetchMode(static::$_origFetchMode);
    }

    protected static function normalizeResult($result)
    {
        static::afterQuery();

        return $result;
        // return array_map(function ($array) {
        // 	return (object) $array;
        // }, array_map('array_change_key_case', $result));
    }

    /**
     * Get the tables based on the database driver.
     *
     * @param [type] $catalog [description]
     *
     * @return [type] [description]
     */
    public static function getTables($catalog = null)
    {
        static::beforeQuery();
        $dbConfig = Config::get(implode('.', [
            'database.connections', Config::get('database.default'),
        ]));
        $catalog = $catalog ?: $dbConfig['database'];
        $cacheKey = 'tables-' . $catalog;

        return Cache::remember($cacheKey, 5, function () {
            return static::normalizeResult(DB::getDoctrineSchemaManager()->listTables());
        });
    }

    /**
     * Get the tables based on the database driver.
     *
     * @param [type] $catalog [description]
     *
     * @return [type] [description]
     */
    public static function getTableNames($catalog = null)
    {
        static::beforeQuery();
        $dbConfig = Config::get(implode('.', [
            'database.connections', Config::get('database.default'),
        ]));
        $catalog = $catalog ?: $dbConfig['database'];
        $cacheKey = 'tables-' . $catalog;

        return Cache::remember($cacheKey, 5, function () use ($catalog) {
            return static::normalizeResult(array_filter(DB::getDoctrineSchemaManager()->listTableNames()), function ($table) {
                $table = explode('.', $table);
                if (count($table) == 1 || $table[0] == $catalog) {
                    return true;
                }
            });
        });
    }

    public static function getIndexes($tableName = null, $dbName = null)
    {
        static::beforeQuery();

        return static::normalizeResult(DB::getDoctrineSchemaManager()->listTableIndexes($tableName));
    }

    public static function getFields($tableName = null, $dbName = null)
    {
        static::beforeQuery();

        return static::normalizeResult(DB::getDoctrineSchemaManager()->listTableColumns($tableName));
    }

    public static function getQueryWithBindings($query)
    {
        $sql = str_replace('?', "'?'", $query->toSql());
        return Str::replaceArray('?', $query->getBindings(), $sql);
    }

    public static function isJoinedOn($query, $table)
    {
        return collect($query->getQuery()->joins)->pluck('table')->contains(function ($value, $key) use ($table) {
            if (is_a($value, \Illuminate\Database\Query\Expression::class)) {
                /** @var Illuminate\Database\Query\Expression $value */
                return $value->getValue() === $table; // $table is something like "table_name AS permissions_table"
            }

            return $value === $table;
        });
    }
}
