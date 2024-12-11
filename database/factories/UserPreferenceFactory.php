<?php

namespace Database\Factories;

use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserPreferenceFactory extends Factory
{
    protected $model = UserPreference::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'sources' => ['CNN', 'BBC', 'Fox News'],
            'categories' => ['Technology', 'Health'],
            'authors' => ['John Doe', 'Jane Doe'],
        ];
    }
}
