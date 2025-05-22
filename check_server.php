<?php

// Script de vérification du statut du serveur
echo "Vérification du serveur...\n\n";

// Vérifier si un processus écoute sur le port 8000
$connection = @fsockopen('127.0.0.1', 8000, $errno, $errstr, 5);

if (is_resource($connection)) {
    echo "SUCCÈS : Le serveur est accessible sur le port 8000.\n";
    fclose($connection);
} else {
    echo "ERREUR : Impossible de se connecter au serveur sur le port 8000.\n";
    echo "Message d'erreur : " . $errstr . " (Code d'erreur : " . $errno . ")\n\n";
    
    echo "Tentative de redémarrage du serveur...\n";
    
    // Vérifier si le serveur est déjà en cours d'exécution
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        exec('taskkill /F /IM php.exe');
    } else {
        exec('pkill php');
    }
    
    echo "Tentative de redémarrage du serveur...\n";
}

echo "\nVérification de la configuration de l'environnement...\n";

// Vérifier si le fichier .env existe
if (file_exists(__DIR__ . '/.env')) {
    echo "Fichier .env trouvé.\n";
    
    // Lire et afficher les variables d'environnement importantes
    $envContent = file_get_contents(__DIR__ . '/.env');
    preg_match('/APP_URL=(.*)/', $envContent, $appUrlMatch);
    preg_match('/SESSION_DRIVER=(.*)/', $envContent, $sessionDriverMatch);
    
    echo "APP_URL: " . (isset($appUrlMatch[1]) ? $appUrlMatch[1] : "Non défini") . "\n";
    echo "SESSION_DRIVER: " . (isset($sessionDriverMatch[1]) ? $sessionDriverMatch[1] : "Non défini") . "\n";
} else {
    echo "ERREUR : Fichier .env non trouvé.\n";
}

echo "\nSuggestions pour résoudre les problèmes :\n";
echo "1. Assurez-vous qu'aucun autre programme n'utilise le port 8000.\n";
echo "2. Vérifiez que les permissions sont correctes pour les dossiers du projet.\n";
echo "3. Redémarrez le serveur avec 'php artisan serve'.\n";
echo "4. Vérifiez les journaux d'erreurs dans 'storage/logs/laravel.log'.\n"; 