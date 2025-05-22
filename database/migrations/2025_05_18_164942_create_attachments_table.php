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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('chemin');
            $table->string('type_mime');
            $table->unsignedBigInteger('size');
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->unsignedBigInteger('comment_id')->nullable();
            $table->timestamps();
            
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('comment_id')->references('id')->on('comments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
