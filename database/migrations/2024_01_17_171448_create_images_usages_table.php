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
        Schema::create('images_usages', function (Blueprint $table) {
            GeneralSchema::generalFields($table);
            $table->string('filename');
            $table->string('path');
            $table->double('size');
            $table->string('type');
            $table->enum('status', ['public', 'deleted'])->default('public');
            $table->string('usage')->nullable();
            $table->uuid('usage_uuid')->nullable();
            GeneralSchema::generalTimeStamp($table);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images_usages');
    }
};
