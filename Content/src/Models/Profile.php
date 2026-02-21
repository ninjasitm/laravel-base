<?php

namespace Nitm\Content\Models;

/**
 *    @SWG\Definition(
 *        definition="Profile",
 *                       @SWG\Property(property="bio", type="string", example="Profile Bio"),
 *   )
 */
class Profile extends BaseModel
{
    protected $table = 'profiles';

    protected $primaryKey = 'user_id';

    protected $fillable = ['bio'];

    protected $hidden = ['user_id', 'created_at', 'updated_at'];

    public static function validate()
    {
        return [
            'bio' => 'required|text',
        ];
    }
    public static function validateUpdate()
    {
        return [
            'bio' => 'required|text',
        ];
    }
}