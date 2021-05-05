<?php

namespace Nitm\Content\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * PageConfig
 */
class PageConfig extends BaseModel
{
    public static $duration = 10;

    public $indexes = [];

    public $items;

    protected $table = 'nitm_pages';

    protected $jsonable = ['config'];

    protected $visible = ['config'];

    protected $fillable = ['config', 'modelName', 'page', 'namespace'];

    protected $casts = [
        'config' => 'array'
    ];

    protected $createdByAuthFields = ['author_id'];

    protected $result;

    /**
     * @inheritDoc
     */
    public static function boot()
    {
        parent::boot();
        static::creating(
            function ($model) {
                $class = $model->page;
                $page = preg_replace('/[^a-zA-Z0-9]|pageconfig/', '_', class_basename($class));
                $model->modelName = class_exists($class) ? class_basename($class) : $model->getGroupName() . ucfirst(Str::camel($page));
                $model->page = 'pageconfig' . preg_replace('/[^a-zA-Z0-9]|pageconfig/', '', strtolower($page));
                $model->namespace = $model->namesapce ?? (class_exists($class) ? (new \ReflectionClass($class))->getNamespaceName() : 'App\Models');
                $model->modelClass = class_exists($class) ? $class : trim($model->namespace, '\\') . "\\" . trim($model->modelName, '\\');
                $model->config = [];
            }
        );

        static::saved(
            function ($model) {
                //Delete the cached page config after saving so that it can be used immediately
                \Cache::forget(static::getCacheKey($model->page));
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function setAttribute($key, $value)
    {
        $key = str_replace('->', '.', $key);
        $attributes = explode('.', $key);
        $configKey = array_shift($attributes);

        // echo $key;
        // print_R([$key, $attributes, implode('.', $attributes), $this->config, $this->attributes]);
        if (!empty($attributes) && $configKey === 'config') {
            Arr::set($this->attributes, $key, $value);
        } else {
            parent::setAttribute($key, $value);
        }
    }

    /**
     * Get Group Name
     *
     * @return string
     */
    public static function getGroupName(): string
    {
        return 'PageConfig';
    }

    /**
     * Get Page
     *
     * @param  mixed $id
     * @return void
     */
    public static function getPage($id)
    {
        $id = strpos($id, 'pageconfig') !== false ? $id : 'pageconfig' . $id;

        return static::query()->where(['page' => $id])->first();
    }

    /**
     * Get the cache key for this model
     *
     * @param  [type] $id     [description]
     * @param  [type] $params [description]
     * @return string         [description]
     */
    protected static function getCacheKey($id = null, $params = []): string
    {
        $id = $id ?: strtolower(class_basename(get_called_class()));
        $params = json_encode($params);
        return implode(
            '-',
            array_merge(
                (array)$params,
                [
                    md5(get_called_class()),
                    $id,
                ]
            )
        );
    }

    /**
     * Convert the raw page object to the underlying model if it exists
     *
     * @return PageConfig
     */
    public function morphPageToModel(): PageConfig
    {
        $modelClass = $this->modelClass;
        if (class_exists($modelClass)) {
            $model = new $modelClass();
            $model->fill($this->attributes);
            $model->config = is_array($model->config) ? $model->config : json_decode($model->config);
            return $model;
        } else {
            return $this;
        }
    }

    /**
     * Return the page configuration.
     *
     * @return array the configuration
     */
    public static function getConfig($id = null, $options = []): array
    {
        $id = $id ?: strtolower(class_basename(get_called_class()));
        $routeParams = $id != 'pageconfigglobal' ? (\Route::current() ? \Route::current()->parameters() : []) : [];
        $key = static::getCacheKey($id, array_merge((array)$_GET, (array)$routeParams));

        return \Cache::remember(
            $key,
            static::$duration,
            function () use ($id, $routeParams, $options) {
                $originalId = $id;
                $id = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $id));
                $page = static::getPage($id);
                if ($page) {
                    $modelClass = $page->modelClass;
                    if (class_exists($modelClass)) {
                        $model = new $modelClass();
                        $model->fill($page->attributes);
                        $model->config = is_array($model->config) ? $model->config : json_decode($model->config);
                        $args = [$options, $routeParams];

                        $ret_val = array_merge(
                            [
                                'id' => $originalId,
                            ],
                            (array) call_user_func_array([$model, 'prepareConfig'], $args)
                        );
                        return $ret_val;
                    } else {
                        return [];
                    }
                }
            }
        );
    }

    /**
     * Get Navigation
     *
     * @return array
     */
    protected function getNavigation(): array
    {
        $ret_val = [];
        if (
            !$this->result || ($this->result && !in_array(
                get_class($this->result),
                [
                    'Illuminate\Pagination\LengthAwarePaginator',
                    'Illuminate\Pagination\Paginator',
                ]
            ))
        ) {
            return $ret_val;
        }
        if ($this->result->nextPageUrl()) {
            $ret_val['next'] = $this->result->nextPageUrl();
        }
        if ($this->result->previousPageUrl()) {
            $ret_val['previous'] = $this->result->previousPageUrl();
        }

        return $ret_val;
    }

    /**
     * Get Pagination
     *
     * @param  mixed $items
     * @return array
     */
    protected function getPagination($items = null): array
    {
        $items = $items ?: $this->items;
        if (
            !is_null($items)
            && !$items instanceof \Illuminate\Pagination\LengthAwarePaginator
            || $items instanceof \Illuminate\Pagination\Paginator
        ) {
            return [];
        }

        return [
            'last' => $items->lastPage(),
            'next' => $items->currentPage() == $items->lastPage() ? $items->lastPage() : $items->currentPage() + 1,
            'previous' => $items->currentPage() == 1 ? 1 : $items->currentPage() - 1,
            'current' => $items->currentPage(),
        ];
    }

    /**
     * Get Showcase Item
     *
     * @param  mixed $config
     * @return array
     */
    protected static function getShowcaseItem($config): array
    {
        extract($config);

        return array_merge(
            $config,
            array_filter(
                [
                    'title' => @$title,
                    'isEvent' => @$isEvent,
                    'isShowcase' => @$isShowcase,
                    'isFeatured' => @$isFeatured,
                    'featureTitle' => @$featureTitle,
                    'pageTitle' => @$pageTitle,
                    'image' => @$image,
                    'isLocalImage' => @$isLocalImage,
                    'description' => @$description,
                ]
            )
        );
    }

    /**
     * Get Namespace Options
     *
     * @return array
     */
    public function getNamespaceOptions(): array
    {
        $ret_val = [];
        $plugins = \System\Classes\PluginManager::instance()->getPlugins();
        foreach ($plugins as $plugin) {
            $namespace = (new \ReflectionClass($plugin))->getNamespaceName();
            $ret_val[$namespace] = $namespace;
        }

        ksort($ret_val);

        return $ret_val;
    }

    /**
     * Get Page Options
     *
     * @return array
     */
    public function getPageModelOptions($directory = 'Models', $namespace = 'App\Models'): array
    {
        $files = preg_grep('/^' . static::getGroupName() . '(\w+).php/', scandir(app_path($directory)));
        $values = array_map(
            function ($model) use ($namespace) {
                return $namespace . '\\' . substr($model, 0, strpos($model, '.'));
            },
            $files
        );
        $models = array_map(
            function ($model) {
                return str_replace('PageConfig', '', class_basename($model));
            },
            $values
        );

        return array_combine($values, $models);
    }

    /**
     * Get Page Options
     *
     * @return array
     */
    public function getPageOptions($directory = 'Models'): array
    {
        $files = preg_grep('/^' . static::getGroupName() . '(\w+).php/', scandir(app_path($directory)));
        $values = array_map(
            function ($model) {
                return strtolower(substr($model, 0, strpos($model, '.')));
            },
            $files
        );
        $models = array_map(
            function ($model) {
                return str_replace(static::getGroupName(), '', substr($model, 0, strpos($model, '.')));
            },
            $files
        );

        return array_combine($values, $models);
    }

    // public function afterCreate()
    // {
    //     $dumper = new YamlDumper();
    //     $configArray = $this->getBaseControllerFormConfig();
    //     $code = $dumper->dump($configArray, 20, 0, false, true);
    //     $modelCode = str_replace('%s', $this->modelName, $this->getPageModelTemplate());
    //     $modelPath = $this->getModelPath($this).'.php';
    //     if (!File::exists($modelPath)) {
    //         $this->writeFile($modelPath, $modelCode);
    //     }
    //     $fieldConfigPath = $this->getModelConfigPath($this).'/fields.yaml';
    //     if (!File::exists($fieldConfigPath)) {
    //         $this->writeFile($fieldConfigPath, 'fields:');
    //     }
    //     $controllerFormConfigPath = $this->getControllerConfigPath($this).'/config_form.yaml';
    //     if (!File::exists($controllerFormConfigPath)) {
    //         $this->writeFile($controllerFormConfigPath, $code);
    //     }
    // }

    // public function afterDelete()
    // {
    //     $directories = [
    //     $this->getModelConfigPath($this).'/fields.yaml',
    //     $this->getControllerConfigPath().'/config_form.yaml',
    //     $this->getModelPath($this).'.php'
    //     ];

    //     foreach ($directories as $directory) {
    //         try {
    //             unlink($directory);
    //         } catch (\Exception $e) {
    //         }
    //     }
    // }

    /**
     * After Fetch
     *
     * @return void
     */
    // public function afterFetch()
    // {
    //     $this->morph();
    // }

    // public function morph()
    // {
    //     $modelName = $this->modelClass;
    //     if (class_exists($modelName)) {
    //         $model = new $modelName();
    //         $this->hasMany = array_merge($this->hasMany, $model->hasMany);
    //         $this->hasOne = array_merge($this->hasOne, $model->hasOne);
    //     }
    // }

    /**
     * Get Page Name Attribute
     *
     * @return string
     */
    public function getPageNameAttribute(): string
    {
        return substr($this->modelName, strlen($this->getGroupName()));
    }

    /**
     * getBaseNamespaceAttribute
     *
     * @return string
     */
    public function getBaseNamespaceAttribute(): string
    {
        $parts = explode('\\', $this->modelClass);
        array_pop($parts);
        return trim(implode('/', array_filter($parts)), '//');
    }

    /**
     * get
     *
     * @return array
     */
    public function get(): array
    {
        return static::getConfig();
    }

    /**
     * take
     *
     * @return self
     */
    public function take()
    {
        return $this;
    }

    /**
     * Faker for querying the API.
     *
     * @return array the configuration
     */
    public static function apiFind($id, $options = [])
    {
        return static::getConfig($id, $options);
    }

    /**
     * Faker for querying the API.
     *
     * @return array the configuration
     */
    public static function apiQuery($options = [], $multiple = false, $query = null)
    {
        return new static();
    }

    /**
     * Prepare Config
     *
     * @param  mixed $config
     * @param  mixed $routeParameters
     * @return void
     */
    public function prepareConfig($config = [], $routeParameters = []): array
    {
        throw new \Exception(__FUNCTION__ . ' needs to be defined by all sub classes');
    }

    /**
     * Get Model
     *
     * @return void
     */
    public function getModel()
    {
        return $this;
    }

    /**
     * Get Table
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }
}