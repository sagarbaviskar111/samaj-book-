<?php

namespace Database\Factories;

use App\Models\Biodata;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Biodata> */
class BiodataFactory extends Factory
{
    protected $model = Biodata::class;

    public function definition(): array
    {
        return [
            'user_id' => fn () => User::factory()->create(['phone' => '7'.fake()->unique()->numerify('#########'), 'status' => 'active'])->id,
            'added_by' => 'self',
            'status' => 'approved',
            'name' => fake()->name(),
            'gender' => 'female',
            'dob' => now()->subYears(25)->toDateString(),
            'height_cm' => 160,
            'marital_status' => 'never_married',
            'district' => 'Pune',
            'contact_phone' => '9123456780',
            'photo_privacy' => 'members',
            'income_range' => '5_10',
            'income_private' => true,
        ];
    }

    public function male(): static { return $this->state(['gender' => 'male']); }
    public function female(): static { return $this->state(['gender' => 'female']); }
    public function pending(): static { return $this->state(['status' => 'pending']); }
    public function forUser(User $user): static { return $this->state(['user_id' => $user->id]); }
}
