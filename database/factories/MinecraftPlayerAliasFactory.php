<?php

namespace Database\Factories;

use App\Entities\Players\Models\MinecraftPlayerAlias;
use Illuminate\Database\Eloquent\Factories\Factory;

class MinecraftPlayerAliasFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MinecraftPlayerAlias::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'alias' => substr($this->faker->userName, 0, 16),
            'registered_at' => $this->faker->dateTimeBetween('-180days', '-1days'),
        ];
    }
}
