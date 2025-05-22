<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;

class UserService
{
    /**
     * Récupérer les utilisateurs selon leur type
     *
     * @param string|null $type Type d'utilisateur (admin, utilisateur, technicien_interne, prestataire)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersByType(?string $type = null)
    {
        $query = User::query();
        
        if ($type) {
            $query->where('type', $type);
        }
        
        return $query->get();
    }
    
    /**
     * Récupérer les techniciens d'un prestataire
     *
     * @param int $prestataireId ID du prestataire
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTechniciensByPrestataire(int $prestataireId)
    {
        return User::where('prestataire_id', $prestataireId)
                 ->where('type', 'technicien_interne')
                 ->get();
    }
    
    /**
     * Créer un nouvel utilisateur
     *
     * @param array $data Données validées de l'utilisateur
     * @return \App\Models\User
     */
    public function createUser(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'telephone' => $data['telephone'] ?? null,
            'type' => $data['type'],
            'is_active' => $data['is_active'] ?? true,
            'prestataire_id' => $data['prestataire_id'] ?? null,
        ]);
        
        // Assigner les rôles selon le type d'utilisateur
        switch ($data['type']) {
            case 'admin':
                $user->assignRole('admin');
                break;
            case 'utilisateur':
                $user->assignRole('utilisateur');
                break;
            case 'technicien_interne':
                $user->assignRole('technicien_interne');
                break;
            case 'prestataire':
                $user->assignRole('prestataire');
                break;
        }
        
        return $user;
    }
    
    /**
     * Mettre à jour un utilisateur existant
     *
     * @param \App\Models\User $user Utilisateur à mettre à jour
     * @param array $data Données validées de l'utilisateur
     * @return \App\Models\User
     */
    public function updateUser(User $user, array $data)
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'telephone' => $data['telephone'] ?? null,
            'is_active' => $data['is_active'] ?? $user->is_active,
            'prestataire_id' => $data['prestataire_id'] ?? $user->prestataire_id,
        ];
        
        // Mettre à jour le mot de passe si fourni
        if (isset($data['password']) && !empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }
        
        $user->update($userData);
        
        // Mettre à jour le type et les rôles si nécessaire
        if (isset($data['type']) && $data['type'] !== $user->type) {
            $user->type = $data['type'];
            $user->save();
            
            // Retirer tous les rôles existants
            $user->syncRoles([]);
            
            // Assigner le nouveau rôle
            switch ($data['type']) {
                case 'admin':
                    $user->assignRole('admin');
                    break;
                case 'utilisateur':
                    $user->assignRole('utilisateur');
                    break;
                case 'technicien_interne':
                    $user->assignRole('technicien_interne');
                    break;
                case 'prestataire':
                    $user->assignRole('prestataire');
                    break;
            }
        }
        
        return $user;
    }
    
    /**
     * Désactiver un utilisateur
     *
     * @param \App\Models\User $user Utilisateur à désactiver
     * @return \App\Models\User
     */
    public function deactivateUser(User $user)
    {
        $user->is_active = false;
        $user->save();
        return $user;
    }
    
    /**
     * Activer un utilisateur
     *
     * @param \App\Models\User $user Utilisateur à activer
     * @return \App\Models\User
     */
    public function activateUser(User $user)
    {
        $user->is_active = true;
        $user->save();
        return $user;
    }
    
    /**
     * Générer et envoyer un lien de réinitialisation de mot de passe
     *
     * @param string $email Email de l'utilisateur
     * @return string
     */
    public function sendPasswordResetLink(string $email)
    {
        return Password::sendResetLink(['email' => $email]);
    }
    
    /**
     * Réinitialiser le mot de passe d'un utilisateur
     *
     * @param array $data Données de réinitialisation du mot de passe
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword(array $data)
    {
        return Password::reset(
            $data,
            function (User $user, string $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );
    }
} 