@props([
    'type'    => 'success',
    'message' => '',
    'timeout' => 3000
])

<div
    x-data="{
        notifications: [],
        position: 'top-end',
        
        init() {
            // Si el componente recibe un mensaje por props al cargar, lo muestra de inmediato
            if ('{{ $message }}'.length > 0) {
                this.add('{{ $message }}', '{{ $type }}');
            }
        },

        add(message, type = 'success') {
            const id = Date.now();
            const notification = {
                id: id,
                type: type,
                message: message,
                show: false,
                width: '100%'
            };

            this.notifications.push(notification);

            // Trigger de entrada y animación de la barra
            setTimeout(() => {
                const index = this.notifications.findIndex(n => n.id === id);
                if (index > -1) {
                    this.notifications[index].show = true;
                    setTimeout(() => {
                        this.notifications[index].width = '0%';
                    }, 50);
                }
            }, 100);

            // Auto-eliminar
            setTimeout(() => {
                this.remove(id);
            }, {{ $timeout }});
        },

        remove(id) {
            const index = this.notifications.findIndex(n => n.id === id);
            if (index > -1) {
                this.notifications[index].show = false;
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 300);
            }
        }
    }"
    @notify.window="add($event.detail.message, $event.detail.type)"
    class="fixed z-[99] flex flex-col gap-3 w-full max-w-sm p-4 pointer-events-none"
    :class="{
        'top-0 right-0': position === 'top-end',
        'top-0 left-0': position === 'top-start',
        'bottom-0 right-0': position === 'bottom-end',
        'bottom-0 left-0': position === 'bottom-start'
    }"
>
    <template x-for="n in notifications" :key="n.id">
        <div
            x-show="n.show"
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-4 opacity-0 sm:translate-y-0 sm:translate-x-4"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0 translate-x-4"
            class="pointer-events-auto relative overflow-hidden rounded-lg bg-white dark:bg-zinc-800 shadow-xl border border-zinc-200 dark:border-zinc-700"
            role="alert"
        >
            <div class="p-4 flex items-center gap-3">
                <!-- Iconos dinámicos según el tipo -->
                <div class="flex-shrink-0">
                    <template x-if="n.type === 'success'">
                        <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </template>
                    <template x-if="n.type === 'error'">
                        <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </template>
                    <template x-if="n.type === 'warning'">
                        <svg class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </template>
                </div>

                <div class="flex-1 text-sm font-medium text-zinc-900 dark:text-zinc-100" x-text="n.message"></div>

                <button @click="remove(n.id)" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                </button>
            </div>

            <!-- Barra de progreso individual -->
            <div class="h-1 w-full bg-zinc-100 dark:bg-zinc-700">
                <div
                    class="h-full transition-all linear"
                    :class="{
                        'bg-green-500': n.type === 'success',
                        'bg-red-500': n.type === 'error',
                        'bg-yellow-500': n.type === 'warning',
                        'bg-blue-500': n.type === 'info'
                    }"
                    :style="`width: ${n.width}; transition-duration: {{ $timeout }}ms;`"
                ></div>
            </div>
        </div>
    </template>
</div>