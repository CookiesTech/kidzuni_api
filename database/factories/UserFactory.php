<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $factory->define(App\User::class, function (Faker\Generator $faker) {
            return [
                'name' => $faker->name,
                'email' => $faker->unique()->email,
                'password' => bcrypt('12345'),
            ];
        });
    }
}
