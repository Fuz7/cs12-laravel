<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Estimate;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Estimate>
 */
class EstimateFactory extends Factory
{
    private function getRandomStatus()
    {
        $estimateStatus = [
            'draft',
            'sent',
            'approved',
            'rejected',
        ];
        return ($estimateStatus[array_rand($estimateStatus)]);
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'customer_id' => Customer::inRandomOrder()->first()->id,
            'job_name' => fake()->words(3, true),
            'site_address' => fake()->streetAddress(),
            'status' => $this->getRandomStatus(),
            'notes' => fake()->sentence(),
        ];
    }
    public function withTasks($count = 3)
    {
        return $this->afterCreating(function (Estimate $estimate) use ($count) {
            $estimate->tasks()->saveMany(Task::factory($count)->make());
        });
    }
}
