<?php

namespace Database\Factories;

use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lawyer>
 */
class LawyerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'telegraph_chat_id' => TelegraphChat::factory(),
            'last_name' => fake('ru_RU')->lastName(),
            'first_name' => fake('ru_RU')->firstName(),
        ];
    }

    public function incomplete(): self
    {
        return $this->state([
            'last_name' => null,
            'first_name' => null,
            'middle_name' => null,
        ]);
    }
}
