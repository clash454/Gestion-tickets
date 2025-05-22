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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id'); // Utilisateur qui fait l'évaluation
            $table->unsignedBigInteger('technicien_id'); // Technicien évalué
            $table->integer('note')->comment('Note de 1 à 5');
            $table->text('commentaire')->nullable();
            $table->timestamps();
            
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('technicien_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
