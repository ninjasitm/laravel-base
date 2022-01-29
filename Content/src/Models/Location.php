<?php

namespace Nitm\Content\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Model;
use Rainlab\Location\Models\Country;
use Rainlab\Location\Models\State;

/**
 * Model.
 */
class Location extends Model
{
    use HasFactory;
    use \October\Rain\Database\Traits\Validation;
    use \Nitm\Content\Traits\Model;

    /*
     * Validation
     */
    public $rules = [
        'address' => 'required',
    ];

    public $fillable = [
        'title', 'city', 'zip', 'address', 'name', 'latitude', 'longitude', 'setLocation', 'state', 'country', 'description',
    ];
    public $visible = [
        'id', 'city', 'zip', 'address', 'title', 'name', 'latitude', 'longitude', 'type',
    ];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model
     */
    public $table = 'nitm_location';

    public $hasOne = [
        'image' => File::class,
        'type'  => [
            LocationType::class,
            'key' => 'type_id',
        ],
    ];

    public $with = ['country', 'state'];

    public $implement = ['RainLab.Location.Behaviors.LocationModel'];

    public function beforeSave()
    {
        unset($this->attributes['street']);
        if (!isset($this->attributes['name'])) {
            unset($this->attributes['street']);
            $this->attributes['name']        = array_get($this->attributes, 'location_name', array_get($this->attributes, 'name'), array_get($this->attributes, 'address'));
            $this->attributes['name']        = $this->attributes['name'] ?: $this->attributes['address'];
            $this->attributes['title']       = array_get($this->attributes, 'title', $this->attributes['name']);
            $this->attributes['title']       = $this->attributes['title'] ?: $this->attributes['name'];
            $this->attributes['description'] = @$this->attributes['description'] ?: $this->attributes['name'];
        }
    }

    public function getPublicIdAttribute()
    {
        return $this->id;
    }

    //Custom set address attribute function to prevent an array of adresses due to autocomplete
    public function setAddressAttribute($address)
    {
        if (is_array($address)) {
            $address = $address[0];
        }
        $this->attributes['address'] = $address;
    }

    /**
     * Sets the "country" relation with the code specified, model lookup used.
     * @param string $code
     */
    public function prepareCountryAttribute($code)
    {
        $country = $code;
        if (!($country instanceof Country)) {
            if (!$country = Country::whereCode($code)
                ->orWhere([
                    'id' => intval($code),
                ])->first()) {
                return;
            }
        }
        return $country;
    }

    /**
     * Sets the "state" relation with the code specified, model lookup used.
     * @param string $code
     */
    public function prepareStateAttribute($code)
    {
        $state = $code;
        if (!($state instanceof State)) {
            if (!$state = State::whereCode($code)
                ->orWhere([
                    'id' => intval($code),
                ])->first()) {
                return;
            }
        }
        return $state;
    }
}
