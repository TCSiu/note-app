<?php

use App\Models\Project;
use App\Models\User;
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
        Schema::create('users_projects', function (Blueprint $table) {
            $table->foreignUuid('user_uuid')->references('uuid')->on('users')->cascadeOnDelete();
            $table->foreignUuid('project_uuid')->references('uuid')->on('projects')->cascadeOnDelete();
            // $table->foreignIdFor(User::class, 'user_uuid')->references('uuid')->on('users')->cascadeOnDelete();
            // $table->foreignIdFor(Project::class, 'project_uuid')->references('uuid')->on('projects')->cascadeOnDelete();
            $table->string('permission');
            $table->boolean('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_projects');
    }
};
