<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->foreignId('author_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreignId('manager_id')->nullable()
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->BigInteger('locked')->nullable();
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
