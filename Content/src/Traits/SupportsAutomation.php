<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use App\Helpers\StringHelper;

trait SupportsAutomation
{
    /**
     * Get the variables for displaying help content on the frontend
     *
     * @return array
     */
    public static function getVariablesForDisplay(): array
    {
        // TODO: Should we cache these results?
        $variables = static::getVariables();
        $result = StringHelper::tokenize($variables);
        return $result;
    }

    protected function prepareMessage($message = null, $replace = [])
    {
        $message = $message ?: $this->defaultMessage;
        $preparedVariables = static::transformVariables($replace);
        return __custom($message, $preparedVariables);
    }

    /**
     * Translate the given message.
     *
     * @param  string $key
     * @param  array  $replace
     * @param  string $locale
     * @return string|array|null
     */
    protected static function __custom($key, $replace = [], $locale = null)
    {
        return StringHelper::__($key, $replace, $locale);
    }

    /**
     * Transform the variables supported by this object into the key values needed for translation
     *
     * i.e.: 'model.title' becomes 'modelTitle' with the $this->model->title value
     *
     * @param array      $replace   The common key values to replace in the message
     * @param array|null $variables supported by this
     *
     * @return mixed
     */
    protected function transformVariables($replace = [], $variables = null)
    {
        $result = [];
        $variables = $variables ?: array_keys(static::getVariables());

        if (is_array($variables)) {
            foreach ($variables as $path) {
                $parsedPath = str_replace(array_keys($replace), array_values($replace), $path);
                $parts = explode('.', $parsedPath);
                $property = array_shift($parts);
                if (property_exists($this, $property)) {
                    $subject = $this->$property;
                    $key = static::toKey($path);
                    if (is_object($subject) && count($parts) === 1) {
                        $value = is_callable($subject) ? call_user_func([$subject, $parts[0]]) : $subject->$parts[0];
                        $result[$key] = $value;
                    } elseif ($subject instanceof Collection && current($parts) == '*') {
                        array_shift($parts);
                        $$result[$key] = $subject->pluck(implode('.', $parts));
                    } elseif (is_object($subject) && !empty($parts)) {
                        $value = method_exists($subject, 'toArray') ? $subject->toArray() : (array)$subject;
                        $result[$key] = Arr::get($value, implode('.', $parts));
                    } elseif (is_array($parts) && !empty($parts)) {
                        $result[$key] = Arr::get($subject, implode('.', $parts));
                    } else {
                        $result[$key] = $subject;
                    }
                    foreach (['ucfirst', 'strtolower', 'strtoupper'] as $transform) {
                        $result[$transform($key)] = $result[$key];
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Get a uniform key that can be used for elements
     *
     * @param string $string
     *
     * @return string
     */
    protected static function toKey($string): string
    {
        return StringHelper::toKey($string);
    }
}
