<?php

namespace Database\Factories;

use App\Models\Biodata;
use App\Models\BiodataPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BiodataPhoto>
 */
class BiodataPhotoFactory extends Factory
{
    protected $model = BiodataPhoto::class;

    public function definition(): array
    {
        return [
            'biodata_id' => Biodata::factory(),
            'path' => 'biodata/demo-'.fake()->unique()->numberBetween(1, 99999).'.jpg',
            'is_primary' => true,
            'sort' => 0,
        ];
    }
}
