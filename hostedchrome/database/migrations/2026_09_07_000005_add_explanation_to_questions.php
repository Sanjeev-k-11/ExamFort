<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('questions') && !Schema::hasColumn('questions', 'explanation')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->text('explanation')->nullable()->after('correct_answer');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('questions') && Schema::hasColumn('questions', 'explanation')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropColumn('explanation');
            });
        }
    }
};
