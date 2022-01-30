<?php

namespace Nitm\Content\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Nitm\Content\NitmContent;
use Nitm\Content\Traits\SetUserId;

/**
 *    @SWG\Definition(
 *        definition="Role",
 *                       @SWG\Property(property="name", type="string", example="Super Admin")
 *   )
 */
class Role extends Model
{
    use SetUserId;

    public $timestamps = false;

    protected $table = "roles";

    protected $fillable = ['name'];

    public $createdByAuthFields = ['created_by_id'];

    // protected $hidden = ["id"];

    public function users()
    {
        return $this->hasMany(NitmContent::userModel());
    }

    public function toArray()
    {
        $attributes = parent::toArray();
        $attributes['id'] = Str::slug($attributes['name']);
        return $attributes;
    }
}