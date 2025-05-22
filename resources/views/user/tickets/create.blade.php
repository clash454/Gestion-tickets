@extends('layouts.app')

@section('header')
    Créer un nouveau ticket
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Formulaire de création de ticket</h3>
        </div>
        
        <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            
            <div>
                <label for="titre" class="block text-sm font-medium text-gray-700">Titre du ticket</label>
                <input type="text" name="titre" id="titre" value="{{ old('titre') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>
                @error('titre')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700">Catégorie</label>
                <select name="category_id" id="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>
                    <option value="">Sélectionnez une catégorie</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                <textarea name="description" id="description" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="attachments" class="block text-sm font-medium text-gray-700">Pièces jointes (optionnelles)</label>
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
            
            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('tickets.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Créer le ticket
                </button>
            </div>
        </form>
    </div>
    
    <script>
        // Prévisualisation des fichiers
        document.getElementById('file-upload').addEventListener('change', function(event) {
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
    </script>
@endsection 