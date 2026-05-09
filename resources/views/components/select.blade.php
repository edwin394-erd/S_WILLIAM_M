@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'buscable' => false,
])

<div {{ $attributes->merge(['class' => 'mb-5 relative']) }} {{-- 'relative' aquí es vital --}}
     x-data="{ 
        open: false,
        search: '',
        selected: @js($selected),
        options: @js($options),
        get filteredOptions() {
            if (this.search === '') return this.options;
            return Object.entries(this.options).reduce((acc, [value, label]) => {
                if (label.toLowerCase().includes(this.search.toLowerCase())) {
                    acc[value] = label;
                }
                return acc;
            }, {});
        },
        displayLabel() {
            return this.options[this.selected] || '{{ $attributes->get('placeholder') ?? 'Seleccione...' }}';
        }
     }"
     @click.away="open = false">

    @if($label)
        <label class="block mb-2.5 text-sm font-medium text-heading">{{ $label }}</label>
    @endif

    <input type="hidden" name="{{ $name }}" :value="selected">

    {{-- Botón / Trigger --}}
    <div @click="open = !open" 
         class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base w-full px-3 py-2.5 shadow-xs cursor-pointer flex justify-between items-center transition-all hover:border-brand">
        <span x-text="displayLabel()" class="truncate"></span>
        <svg class="h-4 w-4 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </div>

    {{-- Lista Desplegable --}}
    <div x-show="open" 
         x-cloak
         {{-- Cambios clave: w-full e inset-x-0 para encajar perfectamente --}}
         class="absolute z-[100] left-0 right-0 w-full mt-1 bg-white border border-gray-300 rounded-base shadow-xl overflow-hidden">
        
        @if($buscable)
            <div class="p-2 border-b border-gray-100 bg-gray-50">
                <input type="text" 
                       x-model="search" 
                       placeholder="Buscar..." 
                       class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-brand focus:border-brand outline-none"
                       @click.stop
                       x-ref="searchInput"
                       x-effect="if (open) setTimeout(() => $refs.searchInput.focus(), 50)">
            </div>
        @endif

        <div class="max-h-60 overflow-y-auto">
            <template x-for="[value, label] in Object.entries(filteredOptions)" :key="value">
                <div @click="selected = value; open = false; search = ''; $dispatch('change', value)" 
                     class="px-3 py-2 text-sm cursor-pointer hover:bg-brand hover:text-white transition-colors"
                     :class="selected == value ? 'bg-brand/10 font-bold text-brand' : 'text-gray-700'"
                     x-text="label">
                </div>
            </template>
            
            <div x-show="Object.keys(filteredOptions).length === 0" class="px-3 py-3 text-sm text-gray-500 italic text-center">
                No hay coincidencias
            </div>
        </div>
    </div>

    @error($name)
        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
    @enderror
</div>