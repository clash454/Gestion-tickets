@extends('layouts.app')

@section('header')
    Mes tickets
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Liste de vos tickets</h3>
            @can('create', App\Models\Ticket::class)
            <a href="{{ route('tickets.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nouveau ticket
            </a>
            @endcan
        </div>
        
        @if($tickets->count() > 0)
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catégorie</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date de création</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($tickets as $ticket)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#{{ $ticket->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $ticket->titre }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $ticket->category->nom }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="ticket-status {{ $ticket->statut }}">
                                        {{ ucfirst($ticket->statut) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $ticket->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @can('view', $ticket)
                                    <a href="{{ route('tickets.show', $ticket->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                                        Voir
                                    </a>
                                    @endcan
                                    
                                    @can('update', $ticket)
                                    <a href="{{ route('tickets.edit', $ticket->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                        Modifier
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4">
                {{ $tickets->links() }}
            </div>
        @else
            <div class="p-6 text-center text-gray-500">
                <p>Aucun ticket trouvé. Créez votre premier ticket pour obtenir de l'aide.</p>
                @can('create', App\Models\Ticket::class)
                <a href="{{ route('tickets.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Créer un ticket
                </a>
                @endcan
            </div>
        @endif
    </div>
@endsection 