<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Órdenes de evento
            </h2>
            {{-- Más adelante aquí podremos poner "Nueva orden" cuando tengamos el create --}}
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    {{-- Mensaje OK --}}
                    @if(session('ok'))
                        <div class="px-3 py-2 mb-4 text-sm text-green-800 bg-green-100 border border-green-200 rounded-md">
                            {{ session('ok') }}
                        </div>
                    @endif

                    {{-- Filtros --}}
                    <form method="GET" action="{{ route('event-orders.index') }}"
                          class="grid items-end grid-cols-1 gap-4 mb-4 md:grid-cols-4 lg:grid-cols-5">
                        {{-- Buscar --}}
                        <div class="md:col-span-2">
                            <x-input-label for="search" value="Buscar" />
                            <x-text-input id="search" name="search" type="text"
                                          placeholder="Folio, cliente o email"
                                          class="block w-full mt-1"
                                          value="{{ $filters['search'] ?? '' }}" />
                        </div>

                        {{-- Estatus --}}
                        <div>
                            <x-input-label for="estatus" value="Estatus" />
                            <select id="estatus" name="estatus"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                <option value="">Todos</option>
                                @foreach($estatusDisponibles as $key => $label)
                                    <option value="{{ $key }}"
                                        @selected(($filters['estatus'] ?? '') === $key)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Fecha desde --}}
                        <div>
                            <x-input-label for="fecha_desde" value="Desde (fecha inicio)" />
                            <x-text-input id="fecha_desde" name="fecha_desde" type="date"
                                          class="block w-full mt-1"
                                          value="{{ $filters['fecha_desde'] ?? '' }}" />
                        </div>

                        {{-- Fecha hasta --}}
                        <div>
                            <x-input-label for="fecha_hasta" value="Hasta (fecha inicio)" />
                            <x-text-input id="fecha_hasta" name="fecha_hasta" type="date"
                                          class="block w-full mt-1"
                                          value="{{ $filters['fecha_hasta'] ?? '' }}" />
                        </div>

                        {{-- Botones --}}
                        <div class="flex gap-2 md:col-span-2 lg:col-span-1">
                            <x-primary-button class="flex-1">
                                Filtrar
                            </x-primary-button>

                            <a href="{{ route('event-orders.index') }}"
                               class="px-3 py-2 text-sm text-gray-700 bg-gray-100 border rounded-md hover:bg-gray-200">
                                Limpiar
                            </a>
                        </div>
                    </form>

                    {{-- Resumen --}}
                    <div class="flex flex-col items-start justify-between mb-4 gap-y-1 md:flex-row md:items-center">
                        <p class="text-sm text-gray-500">
                            Resultados: {{ $orders->total() }}
                        </p>
                        <p class="text-sm text-gray-500">
                            Total de la página: 
                            <span class="font-semibold text-gray-700">
                                ${{ number_format($orders->sum('total'), 2) }}
                            </span>
                        </p>
                    </div>

                    {{-- Tabla --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Folio</th>
                                    <th class="px-4 py-2 text-left">Cliente</th>
                                    <th class="px-4 py-2 text-left">Evento</th>
                                    <th class="px-4 py-2 text-left">Estatus</th>
                                    <th class="px-4 py-2 text-right">Total</th>
                                    <th class="px-4 py-2 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($orders as $order)
                                    <tr>
                                        {{-- Folio --}}
                                        <td class="px-4 py-2 font-mono text-xs text-gray-700">
                                            {{ $order->folio }}
                                        </td>

                                        {{-- Cliente --}}
                                        <td class="px-4 py-2">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $order->cliente_nombre ?: 'Sin nombre' }}
                                            </div>
                                            @if($order->cliente_email)
                                                <div class="text-xs text-gray-500">
                                                    {{ $order->cliente_email }}
                                                </div>
                                            @endif
                                            @if($order->cliente_telefono)
                                                <div class="text-xs text-gray-400">
                                                    {{ $order->cliente_telefono }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Evento (lugar + fecha) --}}
                                        <td class="px-4 py-2">
                                            <div class="text-sm text-gray-900">
                                                {{ $order->lugar ?: 'Sin lugar' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                @if($order->fecha_inicio)
                                                    Inicio: {{ $order->fecha_inicio->format('d/m/Y H:i') }}
                                                @endif
                                                @if($order->fecha_recoleccion)
                                                    · Recolección: {{ $order->fecha_recoleccion->format('d/m/Y H:i') }}
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Estatus --}}
                                        <td class="px-4 py-2">
                                            @php
                                                $color = match($order->estatus) {
                                                    \App\Models\EventOrder::STATUS_BORRADOR    => 'bg-gray-100 text-gray-800',
                                                    \App\Models\EventOrder::STATUS_COTIZACION  => 'bg-blue-100 text-blue-800',
                                                    \App\Models\EventOrder::STATUS_CONFIRMADA  => 'bg-emerald-100 text-emerald-800',
                                                    \App\Models\EventOrder::STATUS_PREPARACION => 'bg-indigo-100 text-indigo-800',
                                                    \App\Models\EventOrder::STATUS_SALIDA      => 'bg-amber-100 text-amber-800',
                                                    \App\Models\EventOrder::STATUS_REGRESO     => 'bg-sky-100 text-sky-800',
                                                    \App\Models\EventOrder::STATUS_CIERRE      => 'bg-green-100 text-green-800',
                                                    default                                   => 'bg-gray-100 text-gray-800',
                                                };
                                            @endphp
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">
                                                {{ $order->estatus_label }}
                                            </span>
                                        </td>

                                        {{-- Total --}}
                                        <td class="px-4 py-2 text-right">
                                            <div class="font-semibold text-gray-900">
                                                ${{ number_format($order->total, 2) }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                Subtotal: ${{ number_format($order->subtotal, 2) }}
                                                · IVA: ${{ number_format($order->impuestos, 2) }}
                                            </div>
                                        </td>

                                        {{-- Acciones --}}
                                        <td class="px-4 py-2 text-right">
                                            <a href="{{ route('event-orders.show', $order) }}"
                                               class="text-xs text-indigo-600 hover:underline">
                                                Ver
                                            </a>
                                            {{-- Más adelante aquí podremos agregar "Editar estatus", PDFs, etc. --}}
                                            <a href="{{ route('event_orders.edit', $order) }}"
                                                class="text-xs font-medium text-gray-700 hover:text-gray-900">
                                                    Editar
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-4 text-sm text-center text-gray-500">
                                            No hay órdenes que coincidan con los filtros.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
