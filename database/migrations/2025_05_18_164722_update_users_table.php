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
        Schema::table('users', function (Blueprint $table) {
            $table->string('telephone')->nullable();
            $table->enum('type', ['admin', 'utilisateur', 'technicien_interne', 'prestataire'])->default('utilisateur');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('prestataire_id')->nullable(); // Pour les techniciens de prestataires
            $table->foreign('prestataire_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['prestataire_id']);
            $table->dropColumn(['telephone', 'type', 'is_active', 'prestataire_id']);
        });
    }
};
