<?php

namespace Database\Factories;

use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->company(),
            'description' => 'Descrip ...',
            'address' => fake()->address(),
            'country' => fake()->country(),
            'city'   => fake()->city(),
            'tel' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'number_of_employees' => rand(5,100),
            'admin_user_id' => AdminUser::inRandomOrder()->first()->id,
        ];
    }
}
