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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('number_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('key', 30);
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('last_value')->default(0);
            $table->timestamps();

            $table->unique(['key', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('number_sequences');
        Schema::dropIfExists('settings');
    }
};
