<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Book;
use App\Enums\ReadingPlanStatus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReadingPlan>
 */
class ReadingPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(ReadingPlanStatus::cases());
        $isCompleted = $status === ReadingPlanStatus::Completed;

        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'target_date' => fake()->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'status' => $status->value,
            'completed_at' => $isCompleted ? fake()->dateTimeBetween('-1 month', 'now') : null,
        ];
    }
}
