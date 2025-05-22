@extends('layouts.app')

@section('header')
    Ticket #{{ $ticket->id }}
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Informations du ticket -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">{{ $ticket->titre }}</h3>
                <span class="ticket-status {{ $ticket->statut }}">
                    {{ ucfirst($ticket->statut) }}
                </span>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Catégorie</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $ticket->category->nom }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Date de création</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Technicien assigné</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $ticket->assignedTo ? $ticket->assignedTo->name : 'Non assigné' }}</p>
                    </div>
                </div>
                
                <div>
                    <p class="text-sm font-medium text-gray-500">Description</p>
                    <div class="mt-1 text-sm text-gray-900 bg-gray-50 p-4 rounded-md whitespace-pre-wrap">{{ $ticket->description }}</div>
                </div>
                
                @if($ticket->attachments->count() > 0)
                    <div>
                        <p class="text-sm font-medium text-gray-500">Pièces jointes</p>
                        <div class="mt-1 flex flex-wrap gap-2">
                            @foreach($ticket->attachments as $attachment)
                                <a href="{{ Storage::url($attachment->chemin) }}" target="_blank" class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                    {{ $attachment->nom }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <div class="flex justify-end">
                    @can('update', $ticket)
                        <a href="{{ route('tickets.edit', $ticket->id) }}" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            Modifier le ticket
                        </a>
                    @endcan
                    
                    @can('changeStatus', $ticket)
                        <div class="ml-3">
                            <form action="{{ route('tickets.change-status', $ticket->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <div class="flex">
                                    <select name="statut" class="rounded-l-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                        <option value="nouveau" {{ $ticket->statut === 'nouveau' ? 'selected' : '' }}>Nouveau</option>
                                        <option value="en_cours" {{ $ticket->statut === 'en_cours' ? 'selected' : '' }}>En cours</option>
                                        <option value="resolu" {{ $ticket->statut === 'resolu' ? 'selected' : '' }}>Résolu</option>
                                        <option value="cloture" {{ $ticket->statut === 'cloture' ? 'selected' : '' }}>Clôturé</option>
                                    </select>
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-r-md">
                                        Changer statut
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Section des commentaires -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Commentaires</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- Liste des commentaires -->
                @if($ticket->comments->count() > 0)
                    <div class="space-y-4">
                        @foreach($ticket->comments as $comment)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $comment->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $comment->user->type === 'technicien_interne' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $comment->user->type === 'technicien_interne' ? 'Technicien' : 'Vous' }}
                                    </span>
                                </div>
                                <div class="mt-2 text-sm text-gray-700 whitespace-pre-wrap">{{ $comment->contenu }}</div>
                                
                                @if($comment->attachments->count() > 0)
                                    <div class="mt-2">
                                        <p class="text-xs font-medium text-gray-500">Pièces jointes</p>
                                        <div class="mt-1 flex flex-wrap gap-2">
                                            @foreach($comment->attachments as $attachment)
                                                <a href="{{ Storage::url($attachment->chemin) }}" target="_blank" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                    </svg>
                                                    {{ $attachment->nom }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-gray-500">
                        Aucun commentaire pour le moment.
                    </div>
                @endif

                <!-- Formulaire d'ajout de commentaire -->
                @can('addComment', $ticket)
                <form action="{{ route('user.tickets.comment', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="contenu" class="block text-sm font-medium text-gray-700">Ajouter un commentaire</label>
                            <textarea name="contenu" id="contenu" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required></textarea>
                            @error('contenu')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="attachments" class="block text-sm font-medium text-gray-700">Pièces jointes (optionnelles)</label>
                            <input type="file" name="attachments[]" id="attachments" multiple class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Envoyer
                            </button>
                        </div>
                    </div>
                </form>
                @endcan
            </div>
        </div>
        
        <!-- Section des devis et factures (visible uniquement pour les techniciens et administrateurs) -->
        @canany(['addDevisOrFacture', 'changeStatus'], $ticket)
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Documents financiers</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- Affichage des devis existants -->
                @if(isset($ticket->devis) && $ticket->devis->count() > 0)
                    <div>
                        <h4 class="text-md font-medium text-gray-700 mb-3">Devis</h4>
                        <div class="space-y-2">
                            @foreach($ticket->devis as $devis)
                                <div class="bg-gray-50 p-3 rounded flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium">Devis #{{ $devis->id }} - {{ $devis->created_at->format('d/m/Y') }}</p>
                                        <p class="text-sm text-gray-500">Montant: {{ number_format($devis->montant, 2, ',', ' ') }} €</p>
                                    </div>
                                    <a href="{{ Storage::url($devis->fichier_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                        Télécharger
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Affichage des factures existantes -->
                @if(isset($ticket->factures) && $ticket->factures->count() > 0)
                    <div>
                        <h4 class="text-md font-medium text-gray-700 mb-3">Factures</h4>
                        <div class="space-y-2">
                            @foreach($ticket->factures as $facture)
                                <div class="bg-gray-50 p-3 rounded flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium">Facture #{{ $facture->id }} - {{ $facture->created_at->format('d/m/Y') }}</p>
                                        <p class="text-sm text-gray-500">Montant: {{ number_format($facture->montant, 2, ',', ' ') }} €</p>
                                        <p class="text-xs text-gray-500">Statut: <span class="{{ $facture->statut === 'valide' ? 'text-green-600' : 'text-yellow-600' }}">{{ ucfirst($facture->statut) }}</span></p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ Storage::url($facture->fichier_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                                            Télécharger
                                        </a>
                                        
                                        @if($facture->statut === 'en_attente')
                                            <a href="{{ route('paiement.payer', $facture) }}" class="inline-flex items-center px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                                </svg>
                                                Payer maintenant
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Formulaire d'ajout de devis/facture (accessible uniquement aux techniciens) -->
                @can('addDevisOrFacture', $ticket)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h4 class="text-md font-medium text-gray-700 mb-3">Ajouter un document</h4>
                    <div class="flex space-x-4">
                        <a href="{{ route('technicien.tickets.devis.create', $ticket->id) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Ajouter un devis
                        </a>
                        <a href="{{ route('technicien.tickets.facture.create', $ticket->id) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Ajouter une facture
                        </a>
                    </div>
                </div>
                @endcan
            </div>
        </div>
        @endcanany
        
        <!-- Section d'évaluation (visible uniquement si le ticket est résolu ou clôturé) -->
        @can('addEvaluation', $ticket)
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Évaluation du service</h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('user.tickets.evaluation', $ticket->id) }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Note (de 1 à 5)</label>
                                <div class="mt-2 flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <label class="mr-3 cursor-pointer">
                                            <input type="radio" name="note" value="{{ $i }}" class="sr-only peer">
                                            <span class="text-2xl peer-checked:text-yellow-500">
                                                ★
                                            </span>
                                        </label>
                                    @endfor
                                </div>
                                @error('note')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="commentaire" class="block text-sm font-medium text-gray-700">Commentaire (optionnel)</label>
                                <textarea name="commentaire" id="commentaire" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"></textarea>
                                @error('commentaire')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Soumettre l'évaluation
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    </div>

    <script>
        // Script pour les étoiles d'évaluation
        document.addEventListener('DOMContentLoaded', function() {
            const ratingInputs = document.querySelectorAll('input[name="note"]');
            const stars = document.querySelectorAll('input[name="note"] + span');
            
            ratingInputs.forEach((input, index) => {
                input.addEventListener('change', () => {
                    const rating = parseInt(input.value);
                    stars.forEach((star, i) => {
                        if (i < rating) {
                            star.classList.add('text-yellow-500');
                        } else {
                            star.classList.remove('text-yellow-500');
                        }
                    });
                });
            });
        });
    </script>
@endsection 