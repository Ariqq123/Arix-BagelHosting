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
        Schema::table('server_tool_runs', function (Blueprint $table) {
            $table->string('view_url')->nullable()->after('download_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('server_tool_runs', function (Blueprint $table) {
            $table->dropColumn('view_url');
        });
    }
};