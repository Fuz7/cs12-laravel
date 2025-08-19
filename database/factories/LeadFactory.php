<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class LeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    private function getRandomLeadSource()
    {
        $leadSources = [
            'Website',
            'Referral',
            'Social Media',
            'Email Campaign',
            'Phone Call',
            'Trade Show',
            'Advertisement',
            'Cold Outreach',
            'Other',
        ];
        return ($leadSources[array_rand($leadSources)]);
    }

    private function getRandomStatus()
    {
        $leadStatus = [
            'new',
            'contacted',
            'qualified',
            'converted',
            'lost',
        ];
        return ($leadStatus[array_rand($leadStatus)]);
    }
    public function definition(): array
    {
        return [
            //
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->email(),
            'phone' => fake()->phoneNumber(),
            'company' => fake()->company(),
            'status' => $this->getRandomStatus(),
            'source' => $this->getRandomLeadSource(),
            'notes'=> fake()->sentence(),
        ];
    }
}
