@extends('layouts.app')

@section('title', 'Crear Equipo')

@section('content')
<div class="bg-white p-6 rounded-lg w-full max-w-md border border-gray-300">
    <form action="{{ route('admin.equipment.store') }}" method="POST">
        @csrf

        <x-input name="name" label="Nombre" placeholder="Nombre del equipo" required />

        <x-confirm-cancel backUrl="{{ route('admin.equipment.index') }}" />
    </form>
</div>
@endsection
