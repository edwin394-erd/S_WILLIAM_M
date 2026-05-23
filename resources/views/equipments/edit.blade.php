@extends('layouts.app')

@section('title', 'Editar Equipo')

@section('content')
<div class="bg-white p-6 rounded-lg w-full max-w-md border border-gray-300">
    <form action="{{ route('admin.equipment.update', $equip->id) }}" method="POST">
        @csrf
        @method('PUT')

        <x-input name="name" label="Nombre" value="{{ $equip->name }}" required />

        <x-confirm-cancel backUrl="{{ route('admin.equipment.index') }}" />
    </form>
</div>
@endsection
