<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{

    private function getRandomLeadSource(){
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

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'first_name'=>fake()->firstName(),
            'last_name'=>fake()->lastName(),
            'email'=>fake()->email(),
            'company_name'=>fake()->company(),
            'phone'=>fake()->phoneNumber(),
            'billing_address'=>fake()->streetAddress(),
            'property_address'=>fake()->streetAddress(),
            'lead_source'=>$this->getRandomLeadSource(),
        ];
    }
}
