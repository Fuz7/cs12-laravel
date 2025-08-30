<?php

namespace Database\Factories;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobFactory extends Factory
{

    protected static $time;
    private function getRandomStatus()
    {
        $estimateStatus = [
            'pending',
            'in_progress',
            'completed',
            'cancelled',
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
        if (! static::$time) {
            static::$time = Carbon::now()->subDays(10);
        }

        $time = static::$time->copy();
        static::$time->addDays(1); // increment
        return [
            //
            'customer_id' => Customer::inRandomOrder()->first()->id,
            'job_name' => fake()->words(3, true),
            "due_date" => $time,
            'site_address' => fake()->streetAddress(),
            'status' => $this->getRandomStatus(),
            'notes' => fake()->sentence(),
        ];
    }
}
