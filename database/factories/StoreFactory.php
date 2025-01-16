<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition()
    {
        return [
            'store_name' => $this->faker->company(),
            'owner_id' => \App\Models\User::factory(),
            'location' => $this->faker->address(),
            'type' => $this->faker->randomElement(['Retail', 'Wholesale']),
            'working_hours' => $this->faker->time() . ' - ' . $this->faker->time(),
            'image' => 'store.jpg',
            'store_email' => $this->faker->email(),
            'store_phone' => $this->faker->phoneNumber(),
            'trn' => $this->faker->unique()->numerify('TRN#########'),
        ];
    }
}
