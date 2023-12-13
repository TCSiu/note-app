<?php

use App\Commons\GeneralSchema;
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
        Schema::create('system_logs', function (Blueprint $table) {
            GeneralSchema::generalFields($table);
            $table->integer('system_logable_id');
            $table->string('system_logable_type');
            $table->bigInteger('user_id')->default(-1);
            $table->string('guard_name')->default('System');
            $table->string('module_name');
            $table->string('action');
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->string('ip_address');
            GeneralSchema::generalTimeStamp($table);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
