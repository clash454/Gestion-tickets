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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description');
            $table->enum('statut', ['nouveau', 'en_cours', 'resolu', 'cloture'])->default('nouveau');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('created_by'); // Utilisateur qui a créé le ticket
            $table->unsignedBigInteger('assigned_to')->nullable(); // Technicien ou prestataire assigné
            $table->unsignedBigInteger('validated_by')->nullable(); // Admin qui a validé
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
            
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('assigned_to')->references('id')->on('users');
            $table->foreign('validated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
