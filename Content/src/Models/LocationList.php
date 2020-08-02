<?php

namespace Nitm\Content\Models;

use Model;

/**
 * Model.
 */
class LocationList extends Model
{
  use \October\Rain\Database\Traits\Validation;
  use \Nitm\Content\Traits\Model;

  /*
     * Validation
     */
  public $rules = [];

  /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
  public $timestamps = false;

  /**
   * @var string The database table used by the model
   */
  public $table = 'nitm_location_list';

  public $fillable = ['item_id', 'location_id', 'item_type', 'location'];

  public $with =  ['location'];

  public $belongsTo = [
    'location' => [
      'Nitm\Content\Models\Location',
      'key' => 'location_id',
      'otherKey' => 'id'
    ],
    'type' => [
      'Nitm\Content\Models\EventType',
      'key' => 'type_id',
      'otherKey' => 'id'
    ],
  ];


  public function setLocationAttribute($location)
  {
    if (count(array_filter($location))) {
      $locationModel = new Location($location);
      $model = Location::firstOrCreate(array_only($locationModel->attributes, ['city', 'zip', 'address']));
      $this->location_id = $model->id;
    }
  }
}
