<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin';
    protected $description = 'Créer un utilisateur administrateur pour Filament';

    public function handle()
    {
        $name = $this->ask('Nom de l\'administrateur');
        $email = $this->ask('Email de l\'administrateur');
        $password = $this->secret('Mot de passe');
        
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
            'type' => 'admin',
            'is_active' => true,
        ]);
        
        $this->info('Utilisateur administrateur créé avec succès!');
        $this->info("Email: {$email}");
        $this->info('Vous pouvez maintenant vous connecter à l\'interface Filament.');
        
        return Command::SUCCESS;
    }
} 