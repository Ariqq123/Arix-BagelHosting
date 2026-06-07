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
        Schema::create('server_tool_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->string('tool', 32);
            $table->string('preset', 64)->nullable();
            $table->string('input_filename', 255)->nullable();
            $table->string('output_filename', 255)->nullable();
            $table->string('host_provider', 32)->nullable();
            $table->string('host_uuid', 64)->nullable();
            $table->text('download_url')->nullable();
            $table->string('sha1', 40)->nullable();
            $table->bigInteger('original_size')->nullable();
            $table->bigInteger('optimized_size')->nullable();
            $table->string('status', 16)->default('pending');
            $table->text('error_message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
            $table->index(['server_id', 'tool']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_tool_runs');
    }
};
