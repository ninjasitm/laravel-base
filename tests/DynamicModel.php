<?php

namespace Tests;
use Nitm\Content\Traits\Model;

class DynamicModel extends \Illuminate\Database\Eloquent\Model
{
    use Model;
    protected $fillable = ['title', 'name', 'is_active'];
    protected $appends = ['full_name'];
    protected $jsonable = ['settings'];
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}