<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

echo "Vérification des utilisateurs administrateurs dans la base de données:\n";
$admins = User::where('type', 'admin')->get();

if ($admins->isEmpty()) {
    echo "Aucun administrateur trouvé dans la base de données.\n";
} else {
    echo "Administrateurs trouvés:\n";
    foreach ($admins as $admin) {
        echo "- ID: " . $admin->id . "\n";
        echo "  Nom: " . $admin->name . "\n";
        echo "  Email: " . $admin->email . "\n";
        echo "  Type: " . $admin->type . "\n";
        echo "  Actif: " . ($admin->is_active ? 'Oui' : 'Non') . "\n";
    }
}

// Vérifier si le package spatie/laravel-permission est activé
if (!class_exists(Role::class)) {
    echo "\nAttention: Le package spatie/laravel-permission n'est pas installé ou activé.\n";
    echo "Impossible d'attribuer des rôles et permissions.\n";
    exit;
}

// Créer le rôle admin s'il n'existe pas
$adminRole = Role::firstOrCreate(['name' => 'admin']);
echo "\nRôle 'admin' " . ($adminRole->wasRecentlyCreated ? 'créé' : 'déjà existant') . ".\n";

// Créer les permissions nécessaires
$permissions = [
    'view_users', 'create_users', 'edit_users', 'delete_users',
    'view_tickets', 'create_tickets', 'edit_tickets', 'delete_tickets',
    'view_categories', 'create_categories', 'edit_categories', 'delete_categories',
    'view_comments', 'create_comments', 'edit_comments', 'delete_comments',
    'view_devis', 'create_devis', 'edit_devis', 'delete_devis',
    'view_factures', 'create_factures', 'edit_factures', 'delete_factures',
    'view_evaluations', 'create_evaluations', 'edit_evaluations', 'delete_evaluations',
    'access_filament'
];

echo "\nCréation/vérification des permissions:\n";
foreach ($permissions as $permissionName) {
    $permission = Permission::firstOrCreate(['name' => $permissionName]);
    echo "- Permission '$permissionName' " . ($permission->wasRecentlyCreated ? 'créée' : 'déjà existante') . ".\n";
    
    // Attribuer la permission au rôle admin
    if (!$adminRole->hasPermissionTo($permissionName)) {
        $adminRole->givePermissionTo($permissionName);
    }
}

// Attribuer le rôle admin à tous les utilisateurs admin
echo "\nAttribution du rôle 'admin' aux utilisateurs administrateurs:\n";
foreach ($admins as $admin) {
    if (!$admin->hasRole('admin')) {
        $admin->assignRole('admin');
        echo "- Rôle 'admin' attribué à {$admin->name} ({$admin->email}).\n";
    } else {
        echo "- {$admin->name} ({$admin->email}) possède déjà le rôle 'admin'.\n";
    }
}

echo "\nProcessus terminé avec succès.\n"; 