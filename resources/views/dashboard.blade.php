<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Resumen general de PuntoFresa
                </p>
            </div>

            <div class="hidden text-sm text-gray-500 md:block">
                {{ now()->format('d/m/Y') }}
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">

            {{-- TARJETAS RESUMEN --}}
            <section class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                {{-- Ventas del día --}}
                <div class="relative overflow-hidden bg-white border border-gray-100 rounded-lg shadow-sm">
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-500">
                                Ventas del día
                            </p>
                            <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-medium text-red-700 rounded-full bg-red-50">
                                $
                            </span>
                        </div>
                        <p class="mt-3 text-2xl font-semibold text-gray-900">
                            {{ $ventasDia ?? '$0.00' }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            Comparado con ayer: <span class="font-medium text-emerald-600">+0%</span>
                        </p>
                    </div>
                </div>

                {{-- Ventas del mes --}}
                <div class="relative overflow-hidden bg-white border border-gray-100 rounded-lg shadow-sm">
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-500">
                                Ventas del mes
                            </p>
                            <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-medium text-white rounded-full bg-slate-900">
                                MXN
                            </span>
                        </div>
                        <p class="mt-3 text-2xl font-semibold text-gray-900">
                            {{ $ventasMes ?? '$0.00' }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ now()->translatedFormat('F Y') }}
                        </p>
                    </div>
                </div>

                {{-- Eventos en curso --}}
                <div class="relative overflow-hidden bg-white border border-gray-100 rounded-lg shadow-sm">
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-500">
                                Eventos en curso
                            </p>
                            <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-medium rounded-full bg-amber-50 text-amber-700">
                                ●
                            </span>
                        </div>
                        <p class="mt-3 text-2xl font-semibold text-gray-900">
                            {{ $eventosEnCurso ?? 0 }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            Órdenes en estatus preparación / salida / regreso
                        </p>
                    </div>
                </div>

                {{-- Eventos cerrados --}}
                <div class="relative overflow-hidden bg-white border border-gray-100 rounded-lg shadow-sm">
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-500">
                                Eventos cerrados (mes)
                            </p>
                            <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-medium rounded-full bg-emerald-50 text-emerald-700">
                                ✓
                            </span>
                        </div>
                        <p class="mt-3 text-2xl font-semibold text-gray-900">
                            {{ $eventosCerradosMes ?? 0 }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            Órdenes en estatus cierre
                        </p>
                    </div>
                </div>
            </section>

            {{-- SEGUNDA FILA: PRÓXIMOS EVENTOS + ACCESOS RÁPIDOS --}}
            <section class="grid gap-6 lg:grid-cols-3">
                {{-- Próximos eventos --}}
                <div class="bg-white border border-gray-100 rounded-lg shadow-sm lg:col-span-2">
                    <div class="flex items-center justify-between px-5 pt-4 pb-3 border-b border-gray-100">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">
                                Próximos eventos
                            </h3>
                            <p class="mt-1 text-xs text-gray-500">
                                Eventos programados para los siguientes días
                            </p>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-100">
                        {{-- Ejemplo estático, luego lo llenas con tus datos --}}
                        @forelse(($proximosEventos ?? []) as $evento)
                            <div class="flex items-center justify-between px-5 py-3 text-sm">
                                <div>
                                    <p class="font-medium text-gray-800">
                                        {{ $evento->cliente_nombre }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-500">
                                        {{ $evento->lugar }} • Folio {{ $evento->folio }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-800">
                                        {{ \Carbon\Carbon::parse($evento->fecha_evento)->format('d/m/Y') }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-500">
                                        {{ ucfirst($evento->estatus) }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-6 text-sm text-gray-500">
                                No hay eventos próximos registrados.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Accesos rápidos --}}
                <div class="bg-white border border-gray-100 rounded-lg shadow-sm">
                    <div class="px-5 pt-4 pb-3 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-800">
                            Accesos rápidos
                        </h3>
                        <p class="mt-1 text-xs text-gray-500">
                            Navega a las secciones más usadas del sistema.
                        </p>
                    </div>

                    <div class="px-5 py-4 space-y-3 text-sm">
                        <a href="{{ route('users.index') }}"
                           class="flex items-center justify-between px-4 py-2.5 transition-colors border rounded-md border-gray-200 hover:border-slate-900 hover:bg-slate-50">
                            <span class="font-medium text-gray-800">Usuarios</span>
                            <span class="text-xs text-gray-500">Ver listado</span>
                        </a>

                        <a href="{{ route('event-orders.index') }}"
                           class="flex items-center justify-between px-4 py-2.5 transition-colors border rounded-md border-gray-200 hover:border-slate-900 hover:bg-slate-50">
                            <span class="font-medium text-gray-800">Órdenes de evento</span>
                            <span class="text-xs text-gray-500">Ver listado</span>
                        </a>

                        <a href="{{ route('petty-cash-sessions.index') }}"
                           class="flex items-center justify-between px-4 py-2.5 transition-colors border rounded-md border-gray-200 hover:border-slate-900 hover:bg-slate-50">
                            <span class="font-medium text-gray-800">Caja chica</span>
                            <span class="text-xs text-gray-500">Gastos y movimientos</span>
                        </a>

                        <a href="{{ route('items.index') }}"
                           class="flex items-center justify-between px-4 py-2.5 transition-colors border rounded-md border-gray-200 hover:border-slate-900 hover:bg-slate-50">
                            <span class="font-medium text-gray-800">Catálogo de ítems</span>
                            <span class="text-xs text-gray-500">Ver / editar</span>
                        </a>
                    </div>
                </div>
            </section>

        </div>
    </div>
</x-app-layout>
