<?php

namespace Nitm\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Fluent;

/*
 * This is the DB helper class and attempts to fill in the gap to allow smoother DB abstraction.
 * @author malcolm@ninjasitm.com
 */


class DbHelper
{
    protected static $_origFetchMode;

    protected static function beforeQuery()
    {
        // static::$_origFetchMode = DB::getFetchMode();
        // DB::setFetchMode(\PDO::FETCH_ASSOC);
    }

    protected static function afterQuery()
    {
        // DB::setFetchMode(static::$_origFetchMode);
    }

    protected static function normalizeResult($result)
    {
        static::afterQuery();

        return collect($result);
        // return array_map(function ($array) {
        // 	return (object) $array;
        // }, array_map('array_change_key_case', $result));
    }

    /**
     * Get the tables based on the database driver.
     *
     * @param string $catalog The name of the database to use. If not specified, the default database will be used.
     *
     * @return Collection
     */
    public static function getTables($catalog = null)
    {
        static::beforeQuery();
        $connection = DB::connection();
        $cacheKey = 'tables-' . $connection->getName() . '-' . $connection->getDatabaseName() . '-' . ($catalog ?? '');

        // Cache only serializable native data; anonymous adapters cannot be serialized by file/Redis caches.
        $tables = Cache::remember($cacheKey, 5, fn () => $connection->getSchemaBuilder()->getTables());

        return static::normalizeResult(array_map(static fn (array $table) => new class($table) extends Fluent {
            public function getName(): string {
                return $this->name;
            }
        }, $tables));
    }

    /**
     * Get the tables based on the database driver.
     *
     * @param string $catalog The name of the database to use. If not specified, the default database will be used.
     *
     * @return Collection
     */
    public static function getTableNames($catalog = null)
    {
        static::beforeQuery();
        $connection = DB::connection();
        $cacheKey = 'table-names-' . $connection->getName() . '-' . $connection->getDatabaseName() . '-' . ($catalog ?? '');

        return Cache::remember($cacheKey, 5, function () use ($connection) {
            // The catalog is a database name, not a PostgreSQL schema. Qualify PostgreSQL
            // names to distinguish identically named tables in different schemas.
            return static::normalizeResult($connection->getSchemaBuilder()->getTableListing(
                null, $connection->getDriverName() === 'pgsql'
            ));
        });
    }

    /**
     * > Get the indexes for a table
     *
     * @param tableName The name of the table to get the indexes for.
     * @param dbName The name of the database to use. If not specified, the default database will be
     * used.
     *
     * @return Collection of indexes for the table.
     */
    public static function getIndexes($tableName = null, $dbName = null)
    {
        static::beforeQuery();

        return static::normalizeResult(array_map(static fn (array $index) => new class($index) extends Fluent {
            public function getName(): string {
                return $this->name;
            }

            public function getColumns(): array {
                return $this->columns;
            }

            public function isUnique(): bool {
                return $this->unique;
            }

            public function isPrimary(): bool {
                return $this->primary;
            }
        }, DB::connection()->getSchemaBuilder()->getIndexes($tableName)))->keyBy(fn ($index) => $index->getName());
    }

    /**
     * > Get the columns for a table
     *
     * @param tableName The name of the table you want to get the columns for.
     * @param dbName The name of the database to use. If not specified, the default database will be
     * used.
     *
     * @return Collection of columns for the table.
     */
    public static function getColumns($tableName = null, $dbName = null)
    {
        static::beforeQuery();

        return static::normalizeResult(array_map(static fn (array $column) => new class($column) extends Fluent {
            public function getName(): string {
                return $this->name;
            }

            public function getNotnull(): bool {
                return ! $this->nullable;
            }
        }, DB::connection()->getSchemaBuilder()->getColumns($tableName)))->keyBy(fn ($column) => $column->getName());
    }

    /**
     * > It returns a list of columns for a given table
     *
     * @param tableName The name of the table you want to get the fields from.
     * @param dbName The name of the database to connect to. If not specified, the default database
     * will be used.
     *
     * @return Collection of column names
     */
    public static function getFields($tableName = null, $dbName = null)
    {
        return static::getColumns($tableName, $dbName);
    }

    /**
     * > Get the foreign key constraints for a table
     *
     * @param tableName The name of the table you want to get the foreign keys for.
     * @param dbName The name of the database to use. If not specified, the default database will be
     * used.
     *
     * @return Collection of foreign keys for the table.
     */
    public static function getForeignConstraints($tableName = null, $dbName = null)
    {
        static::beforeQuery();

        return static::normalizeResult(array_map(static function (array $foreignKey) use ($tableName) {
            // SQLite does not expose names; retain a usable Laravel-style name for lookups.
            $foreignKey['name'] ??= $tableName . '_' . implode('_', $foreignKey['columns']) . '_foreign';

            return new class($foreignKey) extends Fluent {
                public function getName(): string {
                    return $this->name;
                }

                public function getColumns(): array {
                    return $this->columns;
                }
            };
        }, DB::connection()->getSchemaBuilder()->getForeignKeys($tableName)));
    }

    /**
     * It returns a collection of foreign constraint names.
     *
     * @param tableName The name of the table you want to get the foreign keys for.
     * @param dbName The name of the database to look in. If not provided, the default database will be
     * used.
     *
     * @return Collection of foreign key constraints.
     */
    public static function getForeignConstraintNames($tableName = null, $dbName = null)
    {
        return static::getForeignConstraints($tableName, $dbName)->map(
            function ($c) {
                return $c->getName();
            }
        );
    }

    /**
     * It checks if a table has a foreign constraint.
     *
     * @param tableName The name of the table you want to check.
     * @param constraint The name of the foreign key constraint.
     * @param dbName The database name. If not provided, the default database will be used.
     *
     * @return boolean
     */
    public static function hasForeignConstraint($tableName, $constraint, $dbName = null)
    {
        return static::getForeignConstraints($tableName, $dbName)->map(
            function ($fkColumn) {
                return $fkColumn->getName();
            }
        )->flatten()->contains($constraint);
    }

    /**
     * > It returns a boolean value indicating whether the given table has a foreign key constraint on
     * the given columns
     *
     * @param tableName The name of the table you want to check.
     * @param columns The column name(s) you want to check for foreign key constraints.
     * @param dbName The database name. If not provided, it will use the default database.
     *
     * @return boolean
     */
    public static function hasForeignConstraintColumns($tableName, $columns, $dbName = null)
    {
        return static::getForeignConstraints($tableName, $dbName)->contains(
            static fn ($foreignKey) => is_array($columns)
                ? $foreignKey->getColumns() === $columns
                : in_array($columns, $foreignKey->getColumns(), true)
        );
    }

    /**
     * It takes a query builder object and returns a string of the query with the bindings in place
     *
     * @param query The query object you want to get the SQL and bindings for.
     *
     * @return string The query with the bindings replaced with the actual values.
     */
    public static function getQueryWithBindings($query)
    {
        $sql = str_replace('?', "'?'", $query->toSql());
        return Str::replaceArray('?', $query->getBindings(), $sql);
    }

    /**
     * It checks if a query has been joined on a table
     *
     * @param query The query builder instance
     * @param table The table name to check for.
     *
     * @return boolean
     */
    public static function isJoinedOn($query, $table)
    {
        return collect($query->getQuery()->joins)->pluck('table')->contains(
            function ($value, $key) use ($table) {
                if (is_a($value, \Illuminate\Database\Query\Expression::class)) {
                    /** @var Illuminate\Database\Query\Expression $value */
                    return $value->getValue() === $table; // $table is something like "table_name AS permissions_table"
                }

                return $value === $table;
            }
        );
    }
}
