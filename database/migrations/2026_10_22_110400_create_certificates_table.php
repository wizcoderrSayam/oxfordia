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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_number', 40)->unique();
            $table->string('verification_identifier', 64)->unique();
            $table->foreignId('enrollment_id')->constrained()->restrictOnDelete();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->foreignId('replaces_certificate_id')->nullable()->constrained('certificates')->nullOnDelete();
            $table->string('student_name_snapshot');
            $table->string('course_title_snapshot');
            $table->string('grade_snapshot', 20)->nullable();
            $table->date('issue_date');
            $table->foreignId('issued_by')->constrained('users')->restrictOnDelete();
            $table->string('status', 20)->default('valid');
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('revocation_reason')->nullable();
            $table->timestamps();

            $table->index(['enrollment_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
