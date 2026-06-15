<?php

namespace Database\Factories;

use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WriterFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'mobile' => $this->faker->numerify('##########'),
            'password' => bcrypt('password'),
            'user_type' => 'User',
            'Active_Status' => 1,
            'is_deleted' => 0,
        ];
    }

    public function admin()
    {
        return $this->state([
            'user_type' => 'Admin',
        ]);
    }

    public function reseller()
    {
        return $this->state([
            'user_type' => 'Reseller',
        ]);
    }

    public function deleted()
    {
        return $this->state([
            'is_deleted' => 1,
        ]);
    }
}
