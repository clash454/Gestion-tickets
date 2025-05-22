@extends('layouts.app')

@section('header')
    Modifier le ticket #{{ $ticket->id }}
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Modifier les informations du ticket</h3>
        </div>
        
        @cannot('update', $ticket)
            <div class="p-6 bg-red-50 border-b border-red-200">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-medium text-red-800">Vous n'avez pas les permissions nécessaires pour modifier ce ticket.</p>
                </div>
            </div>
        @endcannot
        
        @can('update', $ticket)
        <form action="{{ route('tickets.update', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            @method('PUT')
            
            <div>
                <label for="titre" class="block text-sm font-medium text-gray-700">Titre du ticket</label>
                <input type="text" name="titre" id="titre" value="{{ old('titre', $ticket->titre) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>
                @error('titre')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700">Catégorie</label>
                <select name="category_id" id="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>
                    <option value="">Sélectionnez une catégorie</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $ticket->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->nom }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description détaillée</label>
                <textarea name="description" id="description" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>{{ old('description', $ticket->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Pièces jointes existantes -->
            @if($ticket->attachments->count() > 0)
                <div>
                    <label class="block text-sm font-medium text-gray-700">Pièces jointes existantes</label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($ticket->attachments as $attachment)
                            <div class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-gray-100 text-gray-700">
                                <a href="{{ Storage::url($attachment->chemin) }}" target="_blank" class="flex items-center hover:text-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                    {{ $attachment->nom }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- Nouvelles pièces jointes -->
            <div>
                <label for="attachments" class="block text-sm font-medium text-gray-700">Ajouter de nouvelles pièces jointes (optionnelles)</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span>Téléverser des fichiers</span>
                                <input id="file-upload" name="attachments[]" type="file" multiple class="sr-only">
                            </label>
                            <p class="pl-1">ou glisser-déposer</p>
                        </div>
                        <p class="text-xs text-gray-500">PNG, JPG, PDF, DOC jusqu'à 10MB</p>
                    </div>
                </div>
                <div id="file-preview" class="mt-2 flex flex-wrap gap-2"></div>
                @error('attachments')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('attachments.*')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Actions supplémentaires selon le rôle -->
            @canany(['changeStatus', 'addDevisOrFacture'], $ticket)
            <div class="pt-4 border-t border-gray-200">
                <h4 class="text-md font-medium text-gray-700 mb-3">Actions supplémentaires</h4>
                <div class="flex flex-wrap gap-3">
                    @can('changeStatus', $ticket)
                    <div class="inline-flex items-center">
                        <label for="priority" class="mr-2 text-sm font-medium text-gray-700">Priorité:</label>
                        <select name="priority" id="priority" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                            <option value="basse" {{ old('priority', $ticket->priority ?? '') == 'basse' ? 'selected' : '' }}>Basse</option>
                            <option value="normale" {{ old('priority', $ticket->priority ?? '') == 'normale' ? 'selected' : '' }}>Normale</option>
                            <option value="haute" {{ old('priority', $ticket->priority ?? '') == 'haute' ? 'selected' : '' }}>Haute</option>
                            <option value="urgente" {{ old('priority', $ticket->priority ?? '') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                        </select>
                    </div>
                    @endcan
                    
                    @can('addDevisOrFacture', $ticket)
                    <div class="inline-flex items-center">
                        <label for="estimation_time" class="mr-2 text-sm font-medium text-gray-700">Temps estimé (heures):</label>
                        <input type="number" name="estimation_time" id="estimation_time" value="{{ old('estimation_time', $ticket->estimation_time ?? '') }}" min="0" step="0.5" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    </div>
                    @endcan
                </div>
            </div>
            @endcanany
            
            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('tickets.show', $ticket->id) }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Mettre à jour
                </button>
            </div>
        </form>
        @endcan
    </div>
    
    <script>
        // Prévisualisation des fichiers
        document.addEventListener('DOMContentLoaded', function() {
            const fileUpload = document.getElementById('file-upload');
            if (fileUpload) {
                fileUpload.addEventListener('change', function(event) {
                    const preview = document.getElementById('file-preview');
                    preview.innerHTML = '';
                    
                    const files = event.target.files;
                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        const filePreview = document.createElement('div');
                        filePreview.className = 'bg-gray-100 p-2 rounded flex items-center text-sm';
                        
                        const fileName = document.createElement('span');
                        fileName.className = 'truncate max-w-xs';
                        fileName.textContent = file.name;
                        
                        filePreview.appendChild(fileName);
                        preview.appendChild(filePreview);
                    }
                });
            }
        });
    </script>
@endsection 