<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('placement_exam_candidates', function (Blueprint $table) {
            $table->decimal('mcq_score', 6, 2)->nullable()->after('score');
            $table->decimal('coding_score', 6, 2)->nullable()->after('mcq_score');
            $table->decimal('essay_score', 6, 2)->nullable()->after('coding_score');
            $table->integer('violations_count')->default(0)->after('essay_score');
            $table->integer('trust_score')->default(100)->after('violations_count');
            $table->enum('shortlist_status', ['PENDING', 'SHORTLISTED', 'REJECTED', 'INTERVIEW_ROUND_1', 'INTERVIEW_ROUND_2', 'HIRED'])->default('PENDING')->after('trust_score');
            $table->string('submitted_at', 50)->nullable()->after('shortlist_status');
        });
    }

    public function down(): void
    {
        Schema::table('placement_exam_candidates', function (Blueprint $table) {
            $table->dropColumn([
                'mcq_score',
                'coding_score',
                'essay_score',
                'violations_count',
                'trust_score',
                'shortlist_status',
                'submitted_at',
            ]);
        });
    }
};
