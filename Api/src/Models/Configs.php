<?php namespace Nitm\Api\Models;

use October\Rain\Database\Model;
use October\Rain\Database\Traits\Validation;

class Configs extends Model
{
    use Validation;

    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'nitm_api_configs';
    public $settingsFields = 'fields.yaml';

    public $rules = [
        //'admin_key'     => 'required',
    ];

    public $customMessages = [
        'admin_key.required' => 'Admin Key is required'
    ];

    /**
     * Checks and seeds settings for mismatches
     *
     * @return boolean
     */
    public static function seedSettings()
    {
        if (!self::get('admin_key')) {
            self::set('admin_key', 'write_api_admin_key');
        }

        if (!self::get('output_style')) {
            self::set('output_style', 'json');
        }

        if (!self::get('charsets')) {
            self::set('charsets', 'utf-8');
        }

        if (!self::get('purge_logs_after')) {
            self::set('purge_logs_after', 31);
        }

        if (!self::get('timezone')) {
            self::set('timezone', \Config::get('app.timezone', 'UTC'));
        }

        return true;
    }

    public function getOutputStyleOptions()
    {
        return [
            'json'  => 'JSON',
            'xml'   => 'XML'
        ];
    }

    public function getCharsetsOptions()
    {
        return [
            'utf-8'         => 'utf-8',
            'big5'          => 'big5',
            'euc-kr'        => 'euc-kr',
            'iso-8859-1'    => 'iso-8859-1',
            'iso-8859-2'    => 'iso-8859-2',
            'iso-8859-3'    => 'iso-8859-3',
            'iso-8859-4'    => 'iso-8859-4',
            'iso-8859-5'    => 'iso-8859-5',
            'iso-8859-6'    => 'iso-8859-6',
            'iso-8859-7'    => 'iso-8859-7',
            'iso-8859-8'    => 'iso-8859-8',
            'iso-8859-9'    => 'iso-8859-9',
            'koi8-r'        => 'koi8-r',
            'shift-jis'     => 'shift-jis',
            'x-euc'         => 'x-euc',
            'windows-1250'  => 'windows-1250',
            'windows-1251'  => 'windows-1251',
            'windows-1252'  => 'windows-1252',
            'windows-1253'  => 'windows-1253',
            'windows-1254'  => 'windows-1254',
            'windows-1255'  => 'windows-1255',
            'windows-1256'  => 'windows-1256',
            'windows-1257'  => 'windows-1257',
            'windows-1258'  => 'windows-1258',
            'windows-874'   => 'windows-874',
        ];
    }

    public function getTimezoneOptions()
    {
        $allTimeZones = [];

        foreach (\DateTimeZone::listIdentifiers() as $value) {
            $allTimeZones[$value] = $value;
        }

        return $allTimeZones;
    }
}
