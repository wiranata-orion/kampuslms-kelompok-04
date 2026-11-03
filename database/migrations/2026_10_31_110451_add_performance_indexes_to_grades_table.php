<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            // Optimizes grade summary sorting by graded_at.
            $table->index('graded_at', 'grades_graded_at_index');

            // Optimizes filtering of published grades.
            $table->index('published_at', 'grades_published_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropIndex('grades_graded_at_index');
            $table->dropIndex('grades_published_at_index');
        });
    }
};