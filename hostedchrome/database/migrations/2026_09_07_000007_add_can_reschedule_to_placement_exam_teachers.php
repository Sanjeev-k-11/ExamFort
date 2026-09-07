<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('placement_exam_teachers', function (Blueprint $table) {
            if (!Schema::hasColumn('placement_exam_teachers', 'can_reschedule')) {
                $table->boolean('can_reschedule')->default(false)->after('can_create_questions');
            }
        });
    }

    public function down(): void
    {
        Schema::table('placement_exam_teachers', function (Blueprint $table) {
            if (Schema::hasColumn('placement_exam_teachers', 'can_reschedule')) {
                $table->dropColumn('can_reschedule');
            }
        });
    }
};
