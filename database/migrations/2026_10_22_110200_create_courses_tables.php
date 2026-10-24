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
        Schema::create('course_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('course_categories')->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('course_code', 30)->unique();
            $table->string('short_description', 500)->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('duration_value')->nullable();
            $table->string('duration_unit', 20)->nullable();
            $table->decimal('fee', 10, 2)->nullable();
            $table->string('image_path')->nullable();
            $table->string('syllabus_path')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->index(['status', 'category_id']);
            $table->index('title');
        });

        Schema::create('course_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('max_marks');
            $table->unsignedSmallInteger('pass_marks');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['course_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_modules');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('course_categories');
    }
};
