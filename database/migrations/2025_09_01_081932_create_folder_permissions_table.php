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
        Schema::create('folder_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->constrained('folders')->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->boolean('can_read')->default(true);
            $table->boolean('can_write')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_share')->default(false);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['folder_id', 'unit_id', 'user_id'], 'unique_folder_unit_user');

            $table->index(['folder_id']);
            $table->index(['unit_id']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folder_permissions');
    }
};
