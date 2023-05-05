<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Database\Factories\AttendanceFactory;
use App\Models\Attendance;

class RestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'date' => $this->faker->dateTimeBetween('-1 week', '+1 week'),
            'start_rest' => $this->faker->time(),
            'end_rest' => $this->faker->time(),
        ];
    }
}
