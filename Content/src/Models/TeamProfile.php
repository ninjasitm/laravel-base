<?php

namespace Nitm\Content\Models;

use Nitm\Content\Traits\HasMetadata as HasMetadataTrait;

/**
 * Class TeamProfile
 * @package App\Models
 * @version August 5, 2019, 9:14 pm UTC
 *
 * @property \App\Models\Team team
 * @property string bio
 * @property string tagline
 */
class TeamProfile extends Model
{
    use HasMetadataTrait;

    public $table = 'team_profiles';

    public $fillable = [
        'bio',
        'tagline',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'team_id' => 'integer',
        'bio' => 'string',
        'tagline' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'team_id' => 'required'
    ];
}