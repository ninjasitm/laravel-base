<?php

namespace Nitm\Content\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Nitm\Content\Models\BaseModel as Model;
use Nitm\Content\Repositories\FileRepository as Repository;

/**
 * @SWG\Definition(
 *      definition="File",
 *      required={""},
 *      @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="entity_type",
 *          description="entity_type",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="entity_id",
 *          description="entity_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="name",
 *          description="name",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="type",
 *          description="type",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="fingerprint",
 *          description="fingerprint",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="readable_size",
 *          description="readable_size",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="size",
 *          description="size",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="url",
 *          description="url",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="created_at",
 *          description="created_at",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @SWG\Property(
 *          property="updated_at",
 *          description="updated_at",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @SWG\Property(
 *          property="entity_relation",
 *          description="entity_relation",
 *          type="string"
 *      )
 * )
 */
class File extends Model
{
    /**
     * @var string
     */
    protected $table = 'files';

    /**
     * @var array
     */
    protected $appends = [
        'entity_slug',
        'simple_type',
        'download_url'
    ];

    /**
     * @var array
     */
    public $fillable = [
        'name',
        'type',
        'fingerprint',
        'readable_size',
        'size',
        'url',
        'entity_relation',
        'entity_type',
        'entity_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'entity_type' => 'string',
        'entity_id' => 'integer',
        'name' => 'string',
        'type' => 'string',
        'fingerprint' => 'string',
        'readable_size' => 'string',
        'size' => 'integer',
        'url' => 'string',
        'entity_relation' => 'string'
    ];

    /**
     * Get the entity this file belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function entity(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the duplicate files
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function duplicates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(static::class, 'fingerprint');
    }

    /**
     * Set the name
     *
     * @param mixed $value THe name
     *
     * @return [type]
     */
    public function setNameAttribute($value)
    {
        $parts = pathinfo($value);
        $extlen = strlen(Arr::get($parts, 'extension', '.')) - 1;
        $this->attributes['name'] = strlen($value) > 64 ? substr($parts['basename'], 0, 64 - $extlen) . @$parts['extension'] : $value;
    }

    /**
     * Get the original URL
     *
     * @return string
     */
    public function getRawUrlAttribute(): string
    {
        return Arr::get($this->attributes, 'url') ?? '';
    }

    /**
     * Get the repository url
     *
     * @return string
     */
    public function getUrlAttribute(): string
    {
        $url = Arr::get($this->attributes, 'url');
        if (!strlen($url)) {
            return '';
        }

        return Repository::getUrl($this, $url) ?? '';
    }

    /**
     * Get the url that forces a download of the file
     *
     * @return string
     */
    public function getDownloadUrlAttribute(): string
    {
        $url = Arr::get($this->attributes, 'url');
        if (!strlen($url)) {
            return '';
        }

        return Repository::getUrl($this, $url, true) ?? '';
    }

    /**
     * Get the local storage url
     *
     * @return string
     */
    public function getStorageUrlAttribute(): string
    {
        $url = Arr::get($this->attributes, 'url');
        if (!strlen($url)) {
            return '';
        }

        return Repository::getStorageUrl($url) ?? '';
    }

    /**
     * Get the storage path for this file
     *
     * @return string
     */
    public function getStoragePathAttribute(): string
    {
        $url = Arr::get($this->attributes, 'url');
        if (!strlen($url)) {
            return '';
        }

        return Repository::getStoragePath($url) ?? '';
    }

    /**
     * Get the public storage path for this file
     *
     * @return string
     */
    public function getPublicStoragePathAttribute(): string
    {
        $url = Arr::get($this->attributes, 'url');
        if (!strlen($url)) {
            return '';
        }

        return Repository::getPublicStoragePath($url) ?? '';
    }

    /**
     * Get the simple type
     *
     * @return string
     */
    public function getSimpleTypeAttribute(): string
    {
        return substr($this->type, 0, strpos($this->type, '/')) ?? 'file';
    }

    /**
     * Get the slug for the entity this file is attached to
     *
     * @return string
     */
    public function getEntitySlugAttribute(): string
    {
        return Str::plural(Str::snake(class_basename($this->entity_type))) ?? '';
    }
}