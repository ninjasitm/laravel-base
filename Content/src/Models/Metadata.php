<?php

namespace Nitm\Content\Models;

use File as FileFacade;
use Illuminate\Support\Arr;
use Nitm\Content\Helpers\StringHelper;
use Nitm\Content\Repositories\FileRepository as File;
use Illuminate\Http\UploadedFile;
use Nitm\Content\Models\BaseModel as Model;
use Nitm\Content\Repositories\Files\FileRepository;
use Illuminate\Http\Testing\File as UploadedTestFile;

/**
 * @SWG\Definition(
 *      definition="Metadata",
 *      required={""},
 * @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="entity_type",
 *          description="entity_type",
 *          type="string"
 *      ),
 * @SWG\Property(
 *          property="entity_id",
 *          description="entity_id",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="name",
 *          description="name",
 *          type="string"
 *      ),
 * @SWG\Property(
 *          property="type",
 *          description="type",
 *          type="string"
 *      ),
 * @SWG\Property(
 *          property="fingerprint",
 *          description="fingerprint",
 *          type="string"
 *      ),
 * @SWG\Property(
 *          property="readable_size",
 *          description="readable_size",
 *          type="string"
 *      ),
 * @SWG\Property(
 *          property="size",
 *          description="size",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="url",
 *          description="url",
 *          type="string"
 *      ),
 * @SWG\Property(
 *          property="created_at",
 *          description="created_at",
 *          type="string",
 *          format="date-time"
 *      ),
 * @SWG\Property(
 *          property="updated_at",
 *          description="updated_at",
 *          type="string",
 *          format="date-time"
 *      ),
 * @SWG\Property(
 *          property="entity_relation",
 *          description="entity_relation",
 *          type="string"
 *      )
 * )
 */
class Metadata extends Model
{
    public $table = 'metadata';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DEFAULT_SIMPLE_TYPE = 'application/octet-stream';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'entity_type',
        'entity_id',
        'name',
        'type',
        'value',
        'priority',
        'entity_relation',
        'options',
        'linked_metadata_id',
        'is_required',
        'section',
        'description'
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
        'value' => 'string',
        'priority' => 'integer',
        'entity_relation' => 'string',
        'options' => 'array',
        'linked_metadata_id' => 'integer',
        'is_required' => 'boolean',
        'section' => 'string',
        'description' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'entity_id' => 'required',
        'type' => 'required',
        'is_required' => 'required'
    ];

    protected $attributes = [
        'type' => 'Text'
    ];

    protected $hidden = ['entity_relation', 'entity_id', 'entity_type'];

    protected $with = ['valueFile'];

    protected $appends = ['variable', 'raw_value'];

    /**
     * The file if value is a file
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function valueFile(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->file()->whereEntityRelation('file');
    }

    /**
     * The owner entity
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function entity(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Linked metadata
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function linkedMetadata(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(static::class, 'linked_metadata_id');
    }

    /**
     * Get the default options value
     *
     * @return array
     */
    public function defaultOptions(): array
    {
        return [
            "caption" => "",
            "options" => [],
            "type" => $this->type
        ];
    }

    /**
     * Get the default value value
     *
     * @return mixed
     */
    public function defaultValue()
    {
        switch ($this->type) {
        case 'File':
        case 'Image':
        case 'Video':
            return [
                    'url' => null,
                    'simple_type' => static::DEFAULT_SIMPLE_TYPE
                ];
                break;

        case 'Google Drive':
        case 'Dropdown':
        case 'Checkbox':
        case 'Question':
            return [];
                break;

        default:
            return null;
        }
    }

    /**
     * Set the value attribute properly
     *
     * @param mixed $value
     */
    public function setValueAttribute($value)
    {
        switch ($this->type) {
        case 'File':
        case 'Image':
            if ($value instanceof UploadedFile || $value instanceof UploadedTestFile) {
                $this->storeFileValue($value);
            } elseif (is_array($value) || is_string($value) && preg_match('/^data:image\/(\w+);base64,/', $value)) {
                $url = is_array($value) ? Arr::get($value, 'url') : $value;
                if (is_string($url) && strlen($url) && preg_match('/^data:image\/(\w+);base64,/', $url)) {
                    try {
                        $data = substr($url, strpos($url, ",") + 1);
                        $data = base64_decode($data);
                        $mimeType = FileFacade::streamMimeType($data);
                        $extension = FileFacade::mimeExtension($mimeType);
                        $size = FileFacade::streamSize($data);
                        $name = uniqid('decoded_') . '.' . $extension;
                        $path = tempnam(sys_get_temp_dir(), uniqid('laravel', true));
                        FileFacade::put($path, $data);

                        $file = app(
                            UploadedFile::class,
                            array_merge(
                                is_array($value) ? $value : [],
                                [
                                    'path' => $path,
                                    'originalName' => $name,
                                    'mimeType' => $mimeType,
                                    'size' => $size,
                                    'error' => null
                                ]
                            )
                        );
                        $this->storeFileValue($file);
                    } catch (\Exception $e) {
                        \Log::error($e);
                        $this->attributes['value'] = is_array($value) ? json_encode($value) : $value;
                    }
                } elseif (is_array($value) && Arr::get($value, 'url') == null) {
                    $this->attributes['value'] = json_encode($this->defaultValue());
                } else {
                    $this->attributes['value'] = is_array($value) ? json_encode($value) : $value;
                }
            }
            break;

        case 'Google Drive':
            if (!empty($value) && is_array($value)) {
                $model = $this->valueFile ?? $this->valueFile()->make();
                $model->fill($value);
                $this->saveRelation('valueFile', $model);
            }
            $this->attributes['value'] = is_array($value) ? json_encode($value) : $value;
            break;

        case 'Video':
            $this->attributes['value'] = is_array($value) ? json_encode(
                array_merge(
                    [
                        "url" => ""
                        ], $value
                )
            ) : $value;
            break;

        case 'Textarea':
        case 'TextArea':
        case 'Text':
            // $maxLength = intval(Arr::get($this->options, 'maxLength'));
            // $this->attributes['value'] = $maxLength && $maxLength > 0 ? substr($value, 0, $maxLength) : $value;
            $this->attributes['value'] = $value;
            break;

        default:
            $this->attributes['value'] = is_array($value) ? json_encode($value) : $value;
            break;
        }
    }

    public function getOptionsAttribute()
    {
        $default = $this->defaultOptions();
        $options = Arr::get($this->attributes, 'options');
        $parsed = is_array($options) ? $options : json_decode($options, true) ?? $options;
        // We may have a double encoded value
        $parsed = is_array($parsed) ? $parsed : json_decode($parsed, true) ?? $parsed;
        return is_array($parsed) ? array_merge($default, $parsed) : json_decode($parsed, true) ?? $parsed ?? $default;
    }

    public function getValueAttribute()
    {
        $value = Arr::get($this->attributes, 'value');
        switch ($this->type) {
        case 'File':
        case 'Image':
        case 'Video':
        case 'Google Drive':
            $value = is_array($value) ? $value : (json_decode($value, true) ?? []);
            if (is_array($value)) {
                $value = array_merge($this->defaultValue(), $value);
                $value['simple_type'] = Arr::get($value, 'type') ?? static::DEFAULT_SIMPLE_TYPE;
            }
            if ($this->type !== 'Google Drive' && is_array($value)) {
                $value = $this->ensureValueFileUrl($value);
            }
            break;

        default:
            $value = json_decode($value, true) ?? $value;
            break;
        }
        return $value;
    }

    /**
     * Get the raw value without any HTML or markup
     */
    public function getRawValueAttribute()
    {
        $value = Arr::get($this->attributes, 'value');
        return is_string($value) ? strip_tags($value) : $value;
    }

    /**
     * Get the raw value without any HTML or markup
     */
    public function getRawValueArrayAttribute()
    {
        return json_decode($this->rawValue, true) ?? [];
    }

    /**
     * Get an automatically generated variable attribute
     *
     * @return array || null
     */
    public function getVariableAttribute()
    {
        if ($this->name) {
            $tokenized =  StringHelper::tokenize(
                [
                substr($this->name, 0, 16) . $this->id => 'Value of: ' . $this->name
                ]
            );
            return [
                'value' => key($tokenized),
                'text' => current($tokenized)
            ];
        }
        return null;
    }

    public function getIsEmptyAttribute()
    {
        if ($this->value != null) {
            if (in_array($this->type, ['File', 'Image', 'Google Drive'])) {
                return empty(Arr::get($this->value, 'url'));
            }
        }
        return $this->value == null;
    }

    public function getIsNotEmptyAttribute()
    {
        return !$this->isEmpty;
    }

    public function getSimpleFileType($type)
    {
        return substr($type, 0, strpos($type, '/')) ?? 'file';
    }

    /**
     * @param array $value
     *
     * @return array
     */
    public function ensureValueFileUrl(array $value): array
    {
        if ($url = Arr::get($value, 'url')) {
            $value['url'] = FileRepository::getMetadataUrl($this->id, $value, $url);
            $value['download_url'] = FileRepository::getMetadataUrl($this->id, $value, $url, true);
        }
        return $value;
    }

    public function scopeIsRequired($query)
    {
        return $query->whereIsRequired(true)
            ->orHas(
                'linkedMetadata', function ($query) {
                    $query->isRequired();
                }
            );
    }

    public function scopeIsMissingValue($query)
    {
        return $query->whereNull('value')
            ->orWhere(
                function ($query) {
                    $query->whereIn('type', ['File', 'Image', 'Google Drive'])
                        ->whereRaw("value::jsonb @> '{\"url\":\"\"}'::jsonb");
                }
            );
    }

    public function scopeByPriority($query, $order = 'asc')
    {
        $query->orderBy('priority', $order);
    }

    /**
     * Store an uploaded file
     *
     * @param  UploadedFile $value
     * @return void
     */
    private function storeFileValue(UploadedFile $value): void
    {
        $file = File::store($value, $this->getStorageDirectory('metadata'), 'file', false);
        $file = $file->first() ?? [];
        if (!empty($file)) {
            $model = $this->valueFile ?? $this->valueFile()->make();
            $model->fill($file);
            $this->saveRelation('valueFile', $model);
        }
        $this->attributes['value'] = json_encode($file);
    }
}