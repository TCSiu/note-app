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
        // Schema::create('files_usages', function (Blueprint $table) {
        //     GeneralSchema::generalFields($table);
        //     $table->uuid('file_uuid')->nullable();

        //     GeneralSchema::generalTimeStamp($table);
        // });
        Schema::dropIfExists('files_usages');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files_usages');
    }
};
