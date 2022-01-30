<?php

namespace Nitm\Content\Models;

use App\Models\Model as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @SWG\Definition(
 *      definition="UserProfile",
 *      required={"user_id", "is_public"},
 * @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="user_id",
 *          description="user_id",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="bio",
 *          description="bio",
 *          type="string"
 *      ),
 * @SWG\Property(
 *          property="is_public",
 *          description="is_public",
 *          type="boolean"
 *      ),
 * @SWG\Property(
 *          property="deleted_at",
 *          description="deleted_at",
 *          type="string",
 *          format="date-time"
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
 *          property="dob",
 *          description="dob",
 *          type="string",
 *          format="date"
 *      )
 * )
 */
class UserProfile extends Model
{
    use SoftDeletes;

    public $table = 'user_profiles';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];


    public $fillable = [
        'user_id',
        'bio',
        'is_public',
        'dob'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'bio' => 'string',
        'is_public' => 'boolean',
        'dob' => 'date'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'user_id' => 'required',
        'bio' => 'nullable|string',
        'is_public' => 'required|boolean',
        'deleted_at' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'dob' => 'nullable'
    ];
}