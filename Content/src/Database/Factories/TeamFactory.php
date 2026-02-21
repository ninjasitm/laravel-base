<?php

namespace Nitm\Content\Database\Factories;

use Nitm\Content\Models\Team;
use Nitm\Content\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Team::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->company,
            'user_id' => User::factory()->create()->id,
            'personal_team' => true,
        ];
    }
}