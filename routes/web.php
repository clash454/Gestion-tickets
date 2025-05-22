<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\TicketController as UserTicketController;
use App\Http\Controllers\Technicien\TicketController as TechnicienTicketController;
use App\Http\Controllers\Prestataire\TicketController as PrestataireTicketController;
use App\Http\Controllers\PaiementController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Redirection des pages d'administration vers Filament
Route::middleware(['auth'])->group(function () {
    Route::get('/admin', function () {
        if (auth()->user()->type === 'admin') {
            return redirect('/admin/login');  // Redirige vers la page de login Filament
        }
        return redirect()->route('login');
    })->name('admin.dashboard');

    // Routes nommées pour les ressources Filament
    Route::get('/admin/tickets', function () {
        return redirect()->to('/admin/resources/tickets');
    })->name('admin/tickets');

    Route::get('/admin/users', function () {
        return redirect()->to('/admin/resources/users');
    })->name('admin/users');

    Route::get('/admin/categories', function () {
        return redirect()->to('/admin/resources/categories');
    })->name('admin/categories');
});

Route::get('/', function () {
    if (auth()->check()) {
        switch(auth()->user()->type) {
            case 'admin':
                return redirect('/admin/dashboard');
            case 'utilisateur':
                return redirect('/utilisateur/tickets');
            case 'technicien_interne':
                return redirect('/technicien/tickets');
            case 'prestataire':
                return redirect('/prestataire/tickets');
            default:
                return redirect('/login');
        }
    }
    return redirect('/login');
});

// Routes accessibles uniquement aux utilisateurs connectés
Route::middleware(['auth', 'verified'])->group(function () {
    // Routes pour le profil utilisateur
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Routes pour les utilisateurs standards
    Route::middleware(['role:utilisateur'])->prefix('utilisateur')->group(function () {
        Route::resource('tickets', UserTicketController::class);
        Route::post('tickets/{ticket}/comment', [UserTicketController::class, 'addComment'])->name('user.tickets.comment');
        Route::post('tickets/{ticket}/evaluation', [UserTicketController::class, 'addEvaluation'])->name('user.tickets.evaluation');
    });
    
    // Routes pour les techniciens internes
    Route::middleware(['role:technicien_interne'])->prefix('technicien')->group(function () {
        Route::resource('tickets', TechnicienTicketController::class);
        Route::post('tickets/{ticket}/comment', [TechnicienTicketController::class, 'addComment'])->name('technicien.tickets.comment');
        Route::post('tickets/{ticket}/devis', [TechnicienTicketController::class, 'addDevis'])->name('technicien.tickets.devis');
        Route::post('tickets/{ticket}/facture', [TechnicienTicketController::class, 'addFacture'])->name('technicien.tickets.facture');
    });
    
    // Routes pour les prestataires externes
    Route::middleware(['role:prestataire'])->prefix('prestataire')->group(function () {
        Route::resource('tickets', PrestataireTicketController::class);
        Route::resource('techniciens', PrestataireTicketController::class);
        Route::post('tickets/{ticket}/comment', [PrestataireTicketController::class, 'addComment'])->name('prestataire.tickets.comment');
        Route::post('tickets/{ticket}/devis', [PrestataireTicketController::class, 'addDevis'])->name('prestataire.tickets.devis');
        Route::post('tickets/{ticket}/facture', [PrestataireTicketController::class, 'addFacture'])->name('prestataire.tickets.facture');
    });
    
    // Routes pour le paiement en ligne
    Route::get('/paiement/{facture}', [PaiementController::class, 'payer'])->name('paiement.payer');
    Route::get('/paiement/{facture}/succes', [PaiementController::class, 'succes'])->name('paiement.succes');
    Route::get('/paiement/{facture}/cancel', [PaiementController::class, 'cancel'])->name('paiement.cancel');
});

require __DIR__.'/auth.php';
