<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_access_logs', function (Blueprint $table) {
            $table->id();

            // Log tetap ada sebagai bukti audit walau submission-nya dihapus.
            $table->foreignId('submission_id')
                ->nullable()
                ->constrained('submissions')
                ->nullOnDelete();

            $table->foreignId('user_id')->constrained('users');
            $table->string('action', 20); // download | preview
            $table->string('original_name')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['submission_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_access_logs');
    }
};
