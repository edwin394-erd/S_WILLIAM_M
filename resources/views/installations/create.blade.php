@extends('layouts.app')

@section('title', 'Crear Instalación')

@section('content')
<div class="bg-white p-6 rounded-lg w-full max-w-md border border-gray-300">
    <form action="{{ route('admin.installations.store') }}" method="POST">
        @csrf

        <x-input name="name" label="Nombre" placeholder="Nombre de la instalación" required />
        <x-input name="impact" label="Impacto (Bls)" type="number" min="0" placeholder="Impacto en bls" required />

        <x-confirm-cancel backUrl="{{ route('admin.installations.index') }}" />
    </form>
</div>
@endsection
