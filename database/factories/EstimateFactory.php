<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Estimate;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Estimate>
 */
class EstimateFactory extends Factory
{
    protected static $time;
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

        $streetAddress = $this->getRandomStatus();
        if (! static::$time) {
            static::$time = Carbon::now()->subDays(10);
        }

        $time = static::$time->copy();
        static::$time->subDays(1); // increment
        return [
            //
            'customer_id' => Customer::inRandomOrder()->first()->id,
            'job_name' => fake()->words(3, true),
            'site_address' => fake()->streetAddress(),
            'status' => $streetAddress,
            'approved_at' => $streetAddress === 'approved' ? $time->toDateString() : null,
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
