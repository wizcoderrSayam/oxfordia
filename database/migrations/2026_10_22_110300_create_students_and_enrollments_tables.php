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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('registration_number', 30)->unique();
            $table->string('full_name');
            $table->string('email')->nullable()->index();
            $table->string('phone', 20)->index();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->date('admission_date');
            $table->string('status', 20)->default('active')->index();
            $table->text('internal_notes')->nullable();
            $table->timestamps();

            $table->index('full_name');
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->string('enrollment_number', 30)->unique();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->date('enrollment_date');
            $table->date('start_date')->nullable();
            $table->date('expected_completion_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->string('status', 20)->default('enrolled');
            $table->decimal('total_marks', 8, 2)->nullable();
            $table->unsignedInteger('max_total_marks')->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->string('result', 10)->nullable();
            $table->string('final_grade', 20)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'course_id', 'status']);
            $table->index(['course_id', 'status']);
            $table->index(['status', 'result']);
        });

        Schema::create('enrollment_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_module_id')->constrained()->restrictOnDelete();
            $table->decimal('marks', 6, 2);
            $table->timestamps();

            $table->unique(['enrollment_id', 'course_module_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_marks');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('students');
    }
};
