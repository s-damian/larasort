<?php

namespace SDamian\Tests\Larasort\Fixtures\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use SDamian\Tests\Larasort\Fixtures\Models\Customer;

class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail,
            'last_name' => $this->faker->name,
            'first_name' => $this->faker->name,
        ];
    }
}
