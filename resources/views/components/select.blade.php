@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'buscable' => false,
    'maxVisible' => 10,
    'nullable' => false,
    'nullableLabel' => null,
])

<div {{ $attributes->merge(['class' => 'relative']) }} {{-- 'relative' aquí es vital --}}
    x-data="{ 
        open: false,
        search: '',
        selected: @js($selected),
        options: @js($options),
        maxVisible: @js($maxVisible),
        nullable: @js($nullable),
        nullableLabel: @js($nullableLabel),
        showAll: false,
        dropUp: false,
        get optionEntries() {
            let entries = [];
            if (Array.isArray(this.options)) {
                entries = this.options;
            } else {
                entries = Object.entries(this.options).map(([value, label]) => ({ value, label }));
            }

            if (this.nullable) {
                return [{
                    value: '',
                    label: this.nullableLabel || '{{ $attributes->get('placeholder') ?? 'Seleccione...' }}',
                }, ...entries];
            }

            return entries;
        },
        get filteredOptions() {
            const entries = this.optionEntries;
            if (this.search === '') return entries;
            return entries.filter(option => option.label.toLowerCase().includes(this.search.toLowerCase()));
        },
        get visibleOptions() {
            return this.showAll ? this.filteredOptions : this.filteredOptions.slice(0, this.maxVisible);
        },
        computeDirection() {
            // compute whether dropdown should open upwards based on available space
            const trig = $refs.trigger;
            const drop = $refs.dropdown;
            if (!trig || !drop) return;
            const rect = trig.getBoundingClientRect();
            // estimate dropdown height
            const estHeight = Math.min(drop.scrollHeight || 0, window.innerHeight * 0.6);
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;
            this.dropUp = (spaceBelow < estHeight && spaceAbove > spaceBelow);
            drop.style.maxHeight = estHeight + 'px';
        },

        displayLabel() {
            const selectedOption = this.optionEntries.find(option => option.value === this.selected);
            return selectedOption?.label || '{{ $attributes->get('placeholder') ?? 'Seleccione...' }}';
        }
     }"
     @click.away="open = false">

    @if($label)
        <label class="block mb-2.5 text-sm font-medium text-heading">{{ $label }}</label>
    @endif

    <input type="hidden" name="{{ $name }}" :value="selected">

    {{-- Botón / Trigger --}}
    <div x-ref="trigger" @click="open = !open; if(open) { setTimeout(() => computeDirection(), 50) }" 
         class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base w-full px-3 py-2.5 shadow-xs cursor-pointer flex justify-between items-center transition-all hover:border-brand">
        <span x-text="displayLabel()" class="truncate"></span>
        <svg class="h-4 w-4 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </div>

    {{-- Lista Desplegable --}}
        <div x-show="open" 
            x-cloak
            x-ref="dropdown"
            :class="dropUp ? 'origin-bottom' : 'origin-top'"
            :style="dropUp ? `bottom: calc(100% - 1.8rem)` : `top: calc(100% - 0.25rem)`"
            class="absolute z-[100] left-0 right-0 w-full bg-white border border-gray-300 rounded-base shadow-xl overflow-hidden">
        
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
            <template x-for="option in visibleOptions" :key="option.value">
                <div @click="selected = option.value; open = false; search = ''; $dispatch('change', option.value)" 
                     class="px-3 py-2 text-sm cursor-pointer hover:bg-brand hover:text-white transition-colors"
                     :class="selected == option.value ? 'bg-brand/10 font-bold text-brand' : 'text-gray-700'"
                     x-text="option.label">
                </div>
            </template>

            <div x-show="!showAll && filteredOptions.length > (maxVisible || 0)" class="px-3 py-2 text-sm text-center border-t border-gray-100">
                <button type="button" @click="showAll = true" class="text-sm text-blue-600 hover:underline">Mostrar más (+<span x-text="filteredOptions.length - (maxVisible || 0)"></span>)</button>
            </div>

            <div x-show="filteredOptions.length === 0" class="px-3 py-3 text-sm text-gray-500 italic text-center">
                No hay coincidencias
            </div>
        </div>
    </div>

    @error($name)
        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
    @enderror
</div>