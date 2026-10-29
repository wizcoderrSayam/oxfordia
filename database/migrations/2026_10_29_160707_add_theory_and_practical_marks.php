<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Split module and student marks into theory and practical parts.
     *
     * The existing max_marks, pass_marks and marks columns stay as the stored totals.
     * Existing records become theory-only.
     */
    public function up(): void
    {
        Schema::table('course_modules', function (Blueprint $table) {
            $table->unsignedSmallInteger('theory_max_marks')->default(0)->after('name');
            $table->unsignedSmallInteger('practical_max_marks')->default(0)->after('theory_max_marks');
            $table->unsignedSmallInteger('theory_pass_marks')->default(0)->after('practical_max_marks');
            $table->unsignedSmallInteger('practical_pass_marks')->default(0)->after('theory_pass_marks');
        });

        Schema::table('enrollment_marks', function (Blueprint $table) {
            $table->decimal('theory_marks', 6, 2)->default(0)->after('course_module_id');
            $table->decimal('practical_marks', 6, 2)->default(0)->after('theory_marks');
        });

        DB::table('course_modules')->update([
            'theory_max_marks' => DB::raw('max_marks'),
            'theory_pass_marks' => DB::raw('pass_marks'),
        ]);

        DB::table('enrollment_marks')->update(['theory_marks' => DB::raw('marks')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_marks', function (Blueprint $table) {
            $table->dropColumn(['theory_marks', 'practical_marks']);
        });

        Schema::table('course_modules', function (Blueprint $table) {
            $table->dropColumn(['theory_max_marks', 'practical_max_marks', 'theory_pass_marks', 'practical_pass_marks']);
        });
    }
};
