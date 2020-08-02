<?php namespace Nitm\Api\Facades;

use October\Rain\Support\Facade;

class Api extends Facade
{
    /**
     * Get the registered name of the component.
     * @return string
     */
    protected static function getFacadeAccessor() { return 'api.api'; }
}
