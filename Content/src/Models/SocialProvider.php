<?php
namespace Nitm\Content\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Arr;
use MadWeb\SocialAuth\Models\SocialProvider as BaseSocialProvider;

/**
 * Class SocialProvider.
 *
 * @property      int $id
 * @param         string $slug
 * @param         string $label
 * @property      string $label
 * @property      string $slug
 * @property      array $scopes
 * @property      array $parameters
 * @property      bool $override_scopes
 * @property      bool $stateless
 * @property      string $created_at
 * @property      string $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Nitm\Content\User[] $users
 * @property-read \Illuminate\Database\Eloquent\Collection|\Nitm\Content\Team[] $teams
 * @method        static \Illuminate\Database\Query\Builder|\MadWeb\SocialAuth\Models\SocialProvider whereId($value)
 * @method        static \Illuminate\Database\Query\Builder|\MadWeb\SocialAuth\Models\SocialProvider whereLabel($value)
 * @method        static \Illuminate\Database\Query\Builder|\MadWeb\SocialAuth\Models\SocialProvider whereSlug($value)
 * @method        static \Illuminate\Database\Query\Builder|\MadWeb\SocialAuth\Models\SocialProvider whereCreatedAt($value)
 * @method        static \Illuminate\Database\Query\Builder|\MadWeb\SocialAuth\Models\SocialProvider whereUpdatedAt($value)
 * @mixin         \Eloquent
 */

/**
 * @SWG\Definition(
 *      definition="SocialProvider",
 *      required={"label", "override_scopes", "stateless"},
 * @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="label",
 *          description="label",
 *          type="string"
 *      ),
 * @SWG\Property(
 *          property="slug",
 *          description="slug",
 *          type="string"
 *      ),
 * @SWG\Property(
 *          property="scopes",
 *          description="scopes",
 *          type="string"
 *      ),
 * @SWG\Property(
 *          property="parameters",
 *          description="parameters",
 *          type="string"
 *      ),
 * @SWG\Property(
 *          property="override_scopes",
 *          description="override_scopes",
 *          type="boolean"
 *      ),
 * @SWG\Property(
 *          property="stateless",
 *          description="stateless",
 *          type="boolean"
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
 *      )
 * )
 */
class SocialProvider extends BaseSocialProvider
{
    use HasFactory;

    public $fillable = [
        'label',
        'slug',
        'scopes',
        'parameters',
        'override_scopes',
        'stateless',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'              => 'integer',
        'label'           => 'string',
        'slug'            => 'string',
        'scopes'          => 'array',
        'parameters'      => 'array',
        'override_scopes' => 'boolean',
        'stateless'       => 'boolean',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'label'           => 'required|string|max:255',
        'slug'            => 'nullable|string|max:255',
        'scopes'          => 'nullable|string',
        'parameters'      => 'nullable|string',
        'override_scopes' => 'required|boolean',
        'stateless'       => 'required|boolean',
        'created_at'      => 'nullable',
        'updated_at'      => 'nullable',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function teams()
    {
        return $this->belongsToMany(
            config('nitm-content.team_model', '\\Nitm\Content\\Team'),
            'team_has_social_provider'
        );
    }

    /**
     * To Array
     *
     * @return void
     */
    public function toArray()
    {
        $array          = parent::toArray();
        $array['token'] = Arr::only(Arr::get($array, 'token', []), ['token', 'expires_in']);
        return $array;
    }
}
