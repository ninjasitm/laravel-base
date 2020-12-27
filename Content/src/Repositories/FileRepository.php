<?php

namespace Nitm\Content\Repositories;

use Storage;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Nitm\Content\Models\File;
use Illuminate\Database\Eloquent\Model;
use Nitm\Content\Repositories\BaseRepository;

/**
 * Class FileRepository
 * @package Nitm\Content\Repositories\Files
 * @version October 24, 2019, 10:52 pm UTC
 */

class FileRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'entity_type',
        'entity_id',
        'name',
        'type',
        'fingerprint',
        'readable_size',
        'size',
        'url',
        'entity_relation',
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return File::class;
    }

    /**
     * Create model record
     *
     * @param array || UploadedFile $input
     *
     * @return Model Return an up to date fresh model
     */
    public function create($input)
    {
        $attributes = static::store($input, Arr::get($input, 'entity_relation', 'file'))->first();
        return !empty($attributes) ? File::firstOrCreate($attributes) : null;
    }

    /**
     * Create model records
     *
     * @param array $input
     *
     * @return Model Return an up to date collection of files
     */
    public function createMany($input, $folder = 'files'): \Illuminate\Support\Collection
    {
        return static::store($input, $folder)->map(function ($file) {
            return File::firstOrCreate($file);
        });
    }

    /**
     * Store an array of files and return their public URLs
     *
     * @param [type] $files
     * @param [type] $folder
     * @param string $entityRelation
     * @param bool $public Should the file be public?
     * @param Model $model The model
     *
     * @return \Illuminate\Support\Collection
     */
    public static function store($files, string $folder = 'files', $entityRelation = 'file', $public = true, Model $model = null): \Illuminate\Support\Collection
    {
        $files = is_array($files) && !Arr::isAssoc($files) ? $files : [$files];
        $modelClass = $model ? get_class($model) : null;
        $modelId = $model ? $model->id : null;

        $array = array_filter(array_map(function ($file) use ($public, $folder, $entityRelation, $modelClass, $modelId) {
            if (!($file instanceof UploadedFile) && (is_array($file) && !Arr::get($file, 'url'))) {
                return null;
            }

            if (is_array($file) && Arr::get($file, 'id') !== null) {
                return null;
            }

            try {
                if ($file instanceof UploadedFile) {
                    $disk = static::getStorageDriver();

                    if ($disk == 'local') {
                        static::getStorageDisk()->makeDirectory('public/' . $folder);
                        $filePath = static::getStorageDisk()->put(('public/') . $folder, $file);
                    } else {
                        $filePath = static::getStorageDisk()
                            ->put(
                                $folder,
                                $file,
                                [
                                    'visibility' => $public ? 'public' : null,
                                    'Content-Disposition' => 'attachment; filename=' . $file->getClientOriginalName(),
                                ]
                            );
                    }

                    $url = static::getStorageUrl($filePath, $file->getClientOriginalName(), $file->getMimeType());

                    return [
                        'url' => $url,
                        'name' => $file->getClientOriginalName(),
                        'type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'fingerprint' => md5_file($file->getRealPath()),
                        'readable_size' => static::humanReadableSize($file->getSize()),
                        'entity_type' => $modelClass ?? request()->input('entity_type') ?? Arr::get($file, 'entity_type') ?? 'Nitm\Content\Model',
                        'entity_id' => $modelId ?? request()->input('entity_id') ?? Arr::get($file, 'entity_id') ?? -1,
                        'entity_relation' => Str::camel($entityRelation),
                    ];
                } else {
                    // Most likely an attached file from a third party service such as google
                    return [
                        'id' => Arr::get($file, 'id'),
                        'url' => Arr::get($file, 'url'),
                        'name' => Arr::get($file, 'name'),
                        'type' => Arr::get($file, 'type'),
                        'size' => Arr::get($file, 'size'),
                        'fingerprint' => Arr::get($file, 'fingerprint'),
                        'readable_size' => static::humanReadableSize(Arr::get($file, 'size')),
                        'entity_type' => $modelClass ?? request()->input('entity_type') ?? Arr::get($file, 'entity_type') ??
                            'Nitm\Content\Model',
                        'entity_id' => $modelId ?? request()->input('entity_id') ?? Arr::get($file, 'entity_id') ?? -1,
                        'entity_relation' => Str::camel($entityRelation),

                    ];
                }
            } catch (\Exception $e) {
                if (\App::environment(['dev', 'testing'])) {
                    throw $e;
                }
                \Log::error($e->getMessage());
                return null;
            }
        }, $files));

        return collect($array);
    }

    /**
     * Delete a file
     *
     * @param string $path
     * @param string $disk
     *
     * @return boolean
     */
    public function delete($model)
    {
        $model = $model instanceof File ? $model : File::find($model);
        static::getStorageDisk()->delete($model->url);
        return parent::delete($model->id);
    }

    /**
     * Delete files
     *
     * @param array $paths
     * @param string $disk
     *
     * @return boolean
     */
    public static function deleteMany($paths, $disk = null)
    {
        return !empty($files) ? static::getStorageDisk()->delete(...$paths) : false;
    }

    /**
     * Get the human readable byte size of a file
     *
     * @param integer $bytes
     *
     * @return string
     */
    public static function humanReadableSize($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the file url
     *
     * @param File $model
     * @param string $url
     * @param bool $forDownload
     *
     * @return string
     */
    public static function getUrl(File $model, $url, bool $forDownload = false): string
    {
        $regex = static::getUrlMatchRegex();
        preg_match("/" . $regex . "/", $url, $matches);
        if (count($matches)) {
            if (!static::getIsLocalUrl($url)) {
                // echo "URL isn't local: $url\n";
                // echo "Path should be: " . static::getStoragePath($url) . "\n";
                if (!Str::startsWith($url, 'http') && !Str::startsWith($url, '//')) {
                    $url = static::getStorageConfig('bucket', 'cloud') . $url;
                }
                return static::getCloudStorageDisk()
                    ->temporaryUrl(
                        static::getStoragePath($url),
                        now()->addMinutes(env('AWS_S3_LINK_EXPIRE_MINUTES', 30)),
                        $forDownload ? [
                            'ResponseContentType' => $model->type,
                            'ResponseContentDisposition' => 'attachment; filename=' . $model->name,
                        ] : []
                    );
            }

            $key = $model->name;
            return url("/api/files/{$model->id}/{$key}" . ($forDownload ? "/download" : ''));
        } else {
            return $url;
        }
    }

    /**
     * Get the metadata url
     *
     * @param int $id
     * @param array $value
     * @param string $url
     * @param bool $forDownload
     *
     * @return string
     */
    public static function getMetadataUrl($id, array $value, string $url, bool $forDownload = false): string
    {
        if (!($name = Arr::get($value, 'name')) || !($type = Arr::get($value, 'type'))) {
            return $url;
        }

        $regex = static::getUrlMatchRegex();
        preg_match("/" . $regex . "/", $url, $matches);
        if (count($matches)) {

            if (!static::getIsLocalUrl($url)) {
                // echo "Metadata URL isn't local: $url\n";
                // echo "Metadata Path should be: " . static::getStoragePath($url) . "\n";
                if (!Str::startsWith($url, 'http') && !Str::startsWith($url, '//')) {
                    $url = static::getStorageConfig('bucket', 'cloud') . $url;
                }
                return static::getCloudStorageDisk()
                    ->temporaryUrl(
                        static::getStoragePath($url),
                        now()->addMinutes(env('AWS_S3_LINK_EXPIRE_MINUTES', 30)),
                        $forDownload ? [
                            'ResponseContentType' => Arr::get($value, 'type'),
                            'ResponseContentDisposition' => 'attachment; filename=' . Arr::get($value, 'name'),
                        ] : []
                    );
            }

            $key = Arr::get($value, 'name');
            return url("/api/files/metadata/{$id}/{$key}" . ($forDownload ? "/download" : ''));
        } else {
            return $url;
        }
    }

    /**
     * Get the storage domains supported
     *
     * @return array
     */
    protected static function getStorageDomains(): array
    {
        return [
            'amazonaws.com/' . static::getStorageConfig('bucket', 'cloud'),
            static::getStorageConfig('bucket', 'cloud') . '.s3',
            'localhost',
            'dev.local',
            request()->getHost()
        ];
    }

    /**
     * getUrlMatchRegex
     *
     * @return string
     */
    protected static function getUrlMatchRegex(): string
    {
        return str_replace('\|', '|', preg_quote(implode('|', static::getStorageDomains()), '/'));
    }

    /**
     * Get the fully qualitied URL for a file
     *
     * @param string $path
     * @param string $fileName
     * @param string $type
     * @param bool $forDownload
     *
     * @return string
     */
    public static function getStorageUrl($path, string $fileName, string $type, bool $forDownload = false)
    {
        if (!static::getIsLocalUrl($path)) {
            return static::getCloudStorageDisk()
                ->url(
                    static::getStoragePath($path),
                    $forDownload ? [
                        'ResponseContentType' => $type,
                        'ResponseContentDisposition' => 'attachment; filename=' . $fileName,
                    ] : []
                );
        }

        return Str::startsWith($path, 'http') ? $path : url(static::getStorageDisk()->url($path));
    }

    /**
     * Get the fully qualitied URL for a file
     * @param string $path
     * @return string
     */
    public static function getStoragePath($path)
    {
        $key = stripos($path, 'api/files') > -1 ? 'api/files/' : 'storage/';
        // Switching to supporting temporary urls. Need to verify whether this is a cloud URL
        if (!static::getIsLocalUrl($path)) {
            $path = str_replace(['api/files/'], ['/'], $path);
            $key = static::getStorageConfig('bucket', 'cloud') . '/';
            if (strpos($path, $key) === false && ($host = parse_url($path, PHP_URL_HOST)) !== null) {
                preg_match('/([a-zA-Z0-9\-\_]+.\w+)$/', $host, $matches);
                if (count($matches)) {
                    $key = array_shift($matches) . '/';
                }
            }
        }

        $path = stripos($path, $key) !== false ? substr($path, stripos($path, $key) + strlen($key)) : $path;
        // Find the path
        preg_match('/^(.*\.[a-zA-Z0-9]+)/', $path, $matches);
        if (count($matches)) {
            $path = array_pop($matches);
        } else {
            //If this is a url with arguments/query parameteres then stop at the parameter delimiter
            preg_match('/^(.*)\?/', $path, $matches);
            if (count($matches)) {
                $path = array_pop($matches);
            }
        }

        return ltrim(str_replace(['//'], ['/'], $path), '/');
    }

    /**
     * Get the fully qualitied URL for a file
     * @param string $url
     * @return string
     */
    public static function getPublicStoragePath($url)
    {
        $path = static::getStoragePath($url);
        return static::getIsLocalUrl($url) ? 'public/' . str_replace('public/', '', ltrim($path, '/')) : $path;
    }

    /**
     * @param null $key
     *
     * @return mixed
     */
    public static function getStorageConfig($key = null, $disk = null)
    {
        return config('filesystems.disks.' . config('filesystems.' . ($disk ?? 'default')) . ($key !== null ? ".{$key}" : ''));
    }

    /**
     * Is the storage driver local
     *
     * @return string
     */
    public static function isStorageLocal(): string
    {
        return config('filesystems.default') === 'local';
    }

    /**
     * @param mixed $url
     *
     * @return bool
     */
    public static function getIsLocalUrl($url): bool
    {
        if (static::isStorageLocal() && static::getIsPath($url)) {
            return true;
        }
        return stripos($url, url('/')) !== false || stripos($url, config('app.url')) !== false || stripos($url, '//localhost') !== false || stripos($url, '//127.0.0.1') !== false;
    }

    /**
     * Is the value given a path
     *
     * @param mixed $path
     *
     * @return bool
     */
    public static function getIsPath($path): bool
    {
        return !Str::startsWith($path, 'http') && !Str::startsWith($path, '//');
    }

    /**
     * Get the storage driver
     *
     * @return string
     */
    public static function getStorageDriver(): string
    {
        return config('filesystems.default');
    }

    /**
     * Get the cloud storage driver
     *
     * @return string
     */
    public static function getCloudStorageDriver(): string
    {
        return config('filesystems.cloud');
    }

    /**
     * Get configured storage disk
     *
     * @return string
     */
    public static function getStorageDisk()
    {
        return Storage::disk(static::getStorageDriver());
    }

    /**
     * Get local storage disk
     *
     * @return string
     */
    public static function getLocalStorageDisk()
    {
        return Storage::disk('local');
    }

    /**
     * Get cloud storage disk
     *
     * @return string
     */
    public static function getCloudStorageDisk()
    {

        return Storage::disk(static::getCloudStorageDriver());
    }
}