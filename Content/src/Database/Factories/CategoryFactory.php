<?php

namespace Nitm\Content\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Nitm\Content\Models\Category;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->word()
        ];
    }

    /**
     * Set the category parent
     */
    public function withParent($id): Factory
    {
        return $this->state(function (array $attributes) use ($id) {
            return [
                'parent_id' => $id
            ];
        });
    }
}