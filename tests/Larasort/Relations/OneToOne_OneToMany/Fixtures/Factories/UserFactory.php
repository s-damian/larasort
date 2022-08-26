<?php

namespace SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Fixtures\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Fixtures\Models\User;

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
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail,
            'username' => str_replace(' ', '', $this->faker->unique()->name),
            'last_name' => $this->faker->name,
            'first_name' => $this->faker->name,
        ];
    }
}
