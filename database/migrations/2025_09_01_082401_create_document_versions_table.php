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
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->integer('version_number');
            $table->string('name');
            $table->string('file_path', 500);
            $table->bigInteger('file_size');
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->text('changes_description')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->unique(['document_id', 'version_number'], 'unique_document_version');
            $table->index(['document_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
