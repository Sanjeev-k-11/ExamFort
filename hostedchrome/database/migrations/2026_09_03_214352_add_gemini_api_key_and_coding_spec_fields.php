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
        // 1. Add gemini_api_key to users and organizations table
        if (!Schema::hasColumn('users', 'gemini_api_key')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('gemini_api_key', 255)->nullable()->after('avatar_url');
            });
        }

        if (!Schema::hasColumn('organizations', 'gemini_api_key')) {
            Schema::table('organizations', function (Blueprint $table) {
                $table->string('gemini_api_key', 255)->nullable()->after('status');
            });
        }

        // 2. Add coding specification fields to questions table
        if (!Schema::hasColumn('questions', 'constraints')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->text('constraints')->nullable()->after('question_text');
                $table->text('sample_input')->nullable()->after('constraints');
                $table->text('sample_output')->nullable()->after('sample_input');
                $table->text('explanation')->nullable()->after('sample_output');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'gemini_api_key')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('gemini_api_key');
            });
        }

        if (Schema::hasColumn('organizations', 'gemini_api_key')) {
            Schema::table('organizations', function (Blueprint $table) {
                $table->dropColumn('gemini_api_key');
            });
        }

        if (Schema::hasColumn('questions', 'constraints')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropColumn(['constraints', 'sample_input', 'sample_output', 'explanation']);
            });
        }
    }
};
