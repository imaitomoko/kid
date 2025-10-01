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
        Schema::create('nonmember_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('child_name'); 
            $table->boolean('is_under_3')->nullable();
            $table->foreignId('date_value_id')->constrained()->onDelete('cascade');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable(); 
            $table->boolean('meal')->nullable(); 
            $table->boolean('snack')->nullable(); 
            $table->string('round_type')->nullable(); 
            $table->string('purpose')->nullable(); 
            $table->text('allergy')->nullable(); 
            $table->string('sibling_class')->nullable(); 
            $table->string('sibling_name')->nullable(); 
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nonmember_reservations');
    }
};
