@extends('layouts.app')

@section('header')
    Mes techniciens
@endsection

@section('content')
    <div class="mb-6">
        <a href="{{ route('prestataire.techniciens.create') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">Ajouter un technicien</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($techniciens as $technicien)
            <div class="p-4 bg-white shadow rounded">
                <h3 class="font-bold text-lg">{{ $technicien->name }}</h3>
                <p>{{ $technicien->email }}</p>
                <p>Téléphone : {{ $technicien->telephone }}</p>
            </div>
        @endforeach
    </div>
@endsection 