<?php

namespace Nitm\Content\Models;

/**
 * @SWG\Definition(
 *      definition="RelatedUser",
 *      required={"email"},
 * @SWG\Property(
 *          property="uuid",
 *          description="uuid",
 *          type="string"
 *      ),
 * @SWG\Property(
 *          property="name",
 *          description="name",
 *          type="string"
 *      ),
 * @SWG\Property(
 *          property="email",
 *          description="email",
 *          type="string"
 *      ),
 * @SWG\Property(
 *          property="phone",
 *          description="phone",
 *          type="string"
 *      ),
 * @SWG\Property(
 *          property="username",
 *          description="username",
 *          type="string"
 *      ),
 * @SWG\Property(
 *          property="photo_url",
 *          description="Avatar",
 *          type="string"
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
 * )
 */
class RelatedUser extends User
{
    protected $customWith = [];
}