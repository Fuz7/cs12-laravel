<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->text("first_name");
            $table->text("last_name");
            $table->text("company_name")->nullable();
            $table->text("phone")->nullable();
            $table->text("billing_address")->nullable();
            $table->text("property_address")->nullable();
            $table->text("lead_source")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
