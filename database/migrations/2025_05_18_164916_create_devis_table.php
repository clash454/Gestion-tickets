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
        Schema::create('devis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id'); // Technicien ou prestataire qui a créé le devis
            $table->decimal('montant', 10, 2);
            $table->text('description')->nullable();
            $table->string('fichier_path')->nullable(); // Chemin vers le fichier du devis
            $table->enum('statut', ['en_attente', 'valide', 'refuse'])->default('en_attente');
            $table->unsignedBigInteger('validated_by')->nullable(); // Admin qui a validé
            $table->dateTime('validated_at')->nullable();
            $table->timestamps();
            
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('validated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devis');
    }
};
