@extends('layouts.app')

@section('title', 'Editar Instalación')

@section('content')
<div class="bg-white p-6 rounded-lg w-full max-w-md border border-gray-300">
    <form action="{{ route('admin.installations.update', $installation->id) }}" method="POST">
        @csrf
        @method('PUT')

        <x-input name="name" label="Nombre" value="{{ $installation->name }}" required />
        <x-input name="impact" label="Impacto (Bls)" type="number" value="{{ $installation->impact }}" required />

        <x-confirm-cancel backUrl="{{ route('admin.installations.index') }}" />
    </form>
</div>
@endsection
