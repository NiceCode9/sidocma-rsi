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
        Schema::table('shared_links', function (Blueprint $table) {
            $table->boolean('is_read')->default(false)->after('is_active');
            $table->datetime('read_at')->nullable()->after('is_read');
            $table->string('opened_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shared_links', function (Blueprint $table) {
            $table->dropColumn(['is_read', 'read_at', 'opened_by']);
        });
    }
};
