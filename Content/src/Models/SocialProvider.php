<?php
namespace Nitm\Content\Models;

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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $users
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Team[] $teams
 * @method        static \Illuminate\Database\Query\Builder|\MadWeb\SocialAuth\Models\SocialProvider whereId($value)
 * @method        static \Illuminate\Database\Query\Builder|\MadWeb\SocialAuth\Models\SocialProvider whereLabel($value)
 * @method        static \Illuminate\Database\Query\Builder|\MadWeb\SocialAuth\Models\SocialProvider whereSlug($value)
 * @method        static \Illuminate\Database\Query\Builder|\MadWeb\SocialAuth\Models\SocialProvider whereCreatedAt($value)
 * @method        static \Illuminate\Database\Query\Builder|\MadWeb\SocialAuth\Models\SocialProvider whereUpdatedAt($value)
 * @mixin         \Eloquent
 */
class SocialProvider extends BaseSocialProvider
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function teams()
    {
        return $this->belongsToMany(
            config('nitm-content.team_model', '\\App\\Team'),
            'team_has_social_provider'
        );
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['token'] = Arr::only(Arr::get($array, 'token', []), ['token', 'expires_in']);
        return $array;
    }
}