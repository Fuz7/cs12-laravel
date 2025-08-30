<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected static $time;
    private function getRandomStatus()
    {
        $invoiceStatus = [
            'draft',
            'sent',
            'partially_paid',
            'paid',
            'overdue',
            'cancelled',
        ];
        return ($invoiceStatus[array_rand($invoiceStatus)]);
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
            'paid_amount' => fake()->numberBetween(21, 200),

            'site_address' => fake()->streetAddress(),
            'status' => $this->getRandomStatus(),
            'notes' => fake()->sentence(),
        ];
    }
    public function withTasks($count = 3)
    {
        return $this->afterCreating(function (Invoice $invoice) use ($count) {
            $invoice->tasks()->saveMany(Task::factory($count)->make());
        });
    }
}
