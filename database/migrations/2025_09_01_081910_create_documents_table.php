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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('original_name');
            $table->string('file_path', 500);
            $table->bigInteger('file_size');
            $table->string('mime_type', 100);
            $table->string('extension', 10);
            $table->foreignId('folder_id')->constrained('folders')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->text('description')->nullable();
            $table->integer('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['folder_id']);
            $table->index(['uploaded_by']);
            $table->index(['mime_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
