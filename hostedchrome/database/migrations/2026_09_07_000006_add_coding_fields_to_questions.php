<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('questions')) {
            Schema::table('questions', function (Blueprint $table) {
                if (!Schema::hasColumn('questions', 'sample_input')) {
                    $table->text('sample_input')->nullable()->after('question_text');
                }
                if (!Schema::hasColumn('questions', 'sample_output')) {
                    $table->text('sample_output')->nullable()->after('sample_input');
                }
                if (!Schema::hasColumn('questions', 'constraints')) {
                    $table->text('constraints')->nullable()->after('sample_output');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('questions')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropColumn(['sample_input', 'sample_output', 'constraints']);
            });
        }
    }
};
