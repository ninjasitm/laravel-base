<?php
namespace Nitm\Utils\Classes;
/**
 * This class provides configuration helper functions for config variables
 * @author malcolm@ninjasitm.com
 */


use Config;
use DB;
use Cache;

class ConfigHelper {

	/**
	 * Get the tables based on the database driver
	 * @param  string $key The value to get from the config
	 * @param string $db The name of the database
	 * @return [type]          [description]
	 */
	public static function getDatabaseConfig($key=null, $db='default') {
		return Config::get(implode('.', array_filter([
			'database.connections', Config::get('database.'.$db), $key
		])));
	}
}