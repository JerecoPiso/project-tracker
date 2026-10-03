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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->uuid('pid')->unique();
            $table->string('client_name');
            $table->string('project_name');
            $table->string('description')->nullable();
            $table->enum('status', ['Planning', 'In Progress', 'On Hold', 'Completed'])->default('Planning');
            $table->enum('priority', ['Low', 'Medium', 'High'])->default('Low');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
