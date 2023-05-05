<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'date' => $this->faker->dateTimeBetween('-1 week', '+1 week'),
            'start_work' => $this->faker->time(),
            'end_work' => $this->faker->time(),
        ];
    }
}
