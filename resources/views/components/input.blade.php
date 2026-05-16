@props([
    'name',
    'label' => null,
    'type' => 'text',
])

<div class="{{ $type === 'checkbox' ? 'flex items-center' : '' }} mb-5">
    @if($type !== 'checkbox' && $label)
        <label for="{{ $name }}" class="block mb-2.5 text-sm font-medium text-heading">
            {{ $label }}
        </label>
    @endif

   @if($type === 'textarea')
        <textarea
            id="{{ $name }}"
            name="{{ $name }}"
            {{ $attributes->merge([
                'class' => 'bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body'
            ])->except('value') }}
        >{{ $attributes->get('value', '') }}</textarea>
    @else
        <input 
            type="{{ $type }}" 
            id="{{ $name }}" 
            name="{{ $name }}"
            {{ $attributes->merge([
                'class' => $type === 'checkbox' 
                    ? 'w-4 h-4 border border-default-medium rounded-xs bg-neutral-secondary-medium focus:ring-2 focus:ring-brand-soft' 
                    : 'bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body'
            ]) }}
            {{-- Si es checkbox y no tiene valor definido, ponemos "1" por defecto --}}
            value="{{ $attributes->get('value', ($type === 'checkbox' ? '1' : '')) }}"
        >
    @endif

    @if($type === 'checkbox' && $label)
        <label for="{{ $name }}" class="ms-2 text-sm font-medium text-heading select-none">
            {{ $label }}
        </label>
    @endif



    {{-- Área para mensajes de error de validación de Laravel --}}
    @error($name)
        <span class="text-xs text-red-500 mt-1">{{ $message }}</span>
    @enderror
</div>