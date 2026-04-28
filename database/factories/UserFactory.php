<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'role_id' => DB::table('roles')->where('name', 'Customer')->value('id'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            // Logika untuk mengisi role_id di sini
            'role_id' => DB::table('roles')->where('name', 'Administrator')->value('id')
        ]);
    }

    public function supervisor(): static
    {
        return $this->state(fn (array $attributes) => [
            // Logika untuk mengisi role_id di sini
            'role_id' => DB::table('roles')->where('name', 'Supervisor')->value('id')
        ]);
    }

    public function agent(): static
    {
        return $this->state(fn (array $attributes) => [
            // Logika untuk mengisi role_id di sini
            'role_id' => DB::table('roles')->where('name', 'Agent')->value('id')
        ]);
    }

    public function customer(): static
    {
        return $this->state(fn (array $attributes) => [
            // Logika untuk mengisi role_id di sini
            'role_id' => DB::table('roles')->where('name', 'Customer')->value('id')
        ]);
    }
    
}
