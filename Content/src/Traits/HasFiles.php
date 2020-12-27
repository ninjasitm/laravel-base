<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Nitm\Content\Models\File as FileModel;
use Nitm\Content\Repositories\FileRepository as File;
use Illuminate\Http\UploadedFile;

trait HasFiles
{
    /**
     * Laravel uses this method to allow you to initialize traits
     *
     * @return void
     */
    public function initializeHasFiles()
    {
        $this->addCustomWith(
            'files'
        );
    }

    public function deleteFiles()
    {
        $files = $this->files()->select('url')->get();
        File::deleteMany($files->pluck('url'));
        return $this->files()->delete();
    }

    /**
     * Sync files
     *
     * @param [type] $data
     * @param string $key
     * @param boolean $dataIsValue
     * @param string $entityRelation
     * @return void
     */
    public function syncFiles($data, $key = 'files', $dataIsValue = true, $entityRelation = 'file')
    {
        $data = $dataIsValue ? $data : Arr::get($data, $key);
        $data = Arr::get($data, $key, $data);
        $files = collect([]);
        if (!empty($data) && ($data instanceof UploadedFile || is_array($data))) {
            $stored = File::store($data, $this->getStorageDirectory($key), $entityRelation);
            $files = $this->$key()->createMany($stored->all());
            $this->setRelation($key, $files);
        }
        return $files;
    }

    /**
     * Sync files
     *
     * @param [type] $data
     * @param string $key
     * @param boolean $dataIsValue
     * @param string $entityRelation
     * @return void
     */
    public function syncImages($data, $key = 'images', $dataIsValue = true, $entityRelation = 'image')
    {
        return $this->syncFiles($data, $key, $dataIsValue, $entityRelation);
    }

    /**
     * Sync files
     *
     * @param [type] $data
     * @param string $key
     * @param boolean $dataIsValue
     * @param string $entityRelation
     * @return void
     */
    public function syncFile($data, $key = 'files', $entityRelation = 'file')
    {
        $file = null;
        if (!empty($data) && ($data instanceof UploadedFile || is_array($data))) {
            $stored = File::store($data, $this->getStorageDirectory($key), $entityRelation);
            if ($this->$key) {
                $this->$key->delete();
            }

            $file = $this->$key()->create($stored instanceof FileModel ? $stored : $stored[0]);
        } else {
            $file = $this->$key;
        }

        return $file;
    }

    public function file(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        $baseClass = class_basename(get_class($this));
        $class = '\\Nitm\Content\\Models\\Files\\File';
        if (!class_exists('\\Nitm\Content\\Models\\Files\\' . $baseClass . 'File')) {
            $class = FileModel::class;
        }
        return $this->morphOne($class, 'entity')
            ->orderBy('files.id', 'desc')
            ->groupBy('files.fingerprint')
            ->groupBy('files.id');
    }

    public function image(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->file()->whereEntityRelation('image');
    }

    public function allFiles(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        $baseClass = class_basename(get_class($this));
        $class = '\\Nitm\Content\\Models\\Files\\File';
        if (!class_exists('\\Nitm\Content\\Models\\Files\\' . $baseClass . 'File')) {
            $class = FileModel::class;
        }
        return $this->morphMany($class, 'entity');
    }

    public function files(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->allFiles()->whereEntityRelation('file')
            ->orderBy('files.id', 'desc')
            ->groupBy('files.fingerprint')
            ->groupBy('files.id');
    }

    public function images(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->allFiles()->whereEntityRelation('image')
            ->orderBy('files.id', 'desc')
            ->groupBy('files.fingerprint')
            ->groupBy('files.id');
    }

    /**
     * Get the storage dir for this model
     *
     * @param string $key
     *
     * @return string
     */
    public function getStorageDirectory($key = 'files'): string
    {
        return implode("/", [
            $key,
            Str::snake(class_basename(get_class($this))),
            $this->id
        ]);
    }
}