<?php

namespace Nitm\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * String helper class
 */
class StringHelper
{
    public static function tokenize(array $variables): array
    {
        $result = [];
        $translator = app('customTranslator');
        foreach ($variables as $key => $desc) {
            $result[$translator->tokenize(static::toKey($key))] = $desc;
        }
        return $result;
    }

    /**
     * Translate the given message.
     *
     * @param  string  $key
     * @param  array  $replace
     * @param  string  $locale
     * @return string|array|null
     */
    public static function __($key, $replace = [], $locale = null)
    {
        return app('customTranslator')->get($key, $replace, $locale);
    }

    /**
     * Get a uniform key that can be used for elements
     *
     * @param string $string
     *
     * @return string
     */
    public static function toKey($string): string
    {
        return Str::camel(Str::snake(str_replace('.', '-', $string)));
    }
}
