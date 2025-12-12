<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-400 uppercase">
                    Orden de evento
                </p>
                <h2 class="flex items-center gap-3 text-xl font-semibold leading-tight text-gray-800">
                    Folio {{ $order->folio }}

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
                </h2>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('event-orders.index') }}"
                   class="px-3 py-1 text-sm text-gray-700 bg-gray-100 border rounded-md hover:bg-gray-200">
                    Volver al listado
                </a>

                {{-- Más adelante aquí podemos poner botones para PDF, cambiar estatus, etc. --}}
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto space-y-6 sm:px-6 lg:px-8">

            {{-- Resumen superior --}}
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        {{-- Cliente --}}
                        <div>
                            <h3 class="mb-2 text-sm font-semibold text-gray-600 uppercase">
                                Datos del cliente
                            </h3>
                            <dl class="space-y-1 text-sm text-gray-700">
                                <div class="flex">
                                    <dt class="w-32 font-medium">Nombre</dt>
                                    <dd>{{ $order->cliente_nombre ?: '—' }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 font-medium">Email</dt>
                                    <dd>{{ $order->cliente_email ?: '—' }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 font-medium">Teléfono</dt>
                                    <dd>{{ $order->cliente_telefono ?: '—' }}</dd>
                                </div>
                            </dl>

                            @if($order->contacto_nombre || $order->contacto_telefono)
                                <h4 class="mt-4 mb-1 text-xs font-semibold tracking-wide text-gray-500 uppercase">
                                    Contacto del evento
                                </h4>
                                <dl class="space-y-1 text-sm text-gray-700">
                                    <div class="flex">
                                        <dt class="w-32 font-medium">Nombre</dt>
                                        <dd>{{ $order->contacto_nombre ?: '—' }}</dd>
                                    </div>
                                    <div class="flex">
                                        <dt class="w-32 font-medium">Teléfono</dt>
                                        <dd>{{ $order->contacto_telefono ?: '—' }}</dd>
                                    </div>
                                </dl>
                            @endif
                        </div>

                        {{-- Evento --}}
                        <div>
                            <h3 class="mb-2 text-sm font-semibold text-gray-600 uppercase">
                                Información del evento
                            </h3>
                            <dl class="space-y-1 text-sm text-gray-700">
                                <div class="flex">
                                    <dt class="w-32 font-medium">Lugar</dt>
                                    <dd>{{ $order->lugar ?: '—' }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 font-medium">Dirección</dt>
                                    <dd>{{ $order->direccion ?: '—' }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 font-medium">Entrega</dt>
                                    <dd>
                                        @if($order->fecha_entrega)
                                            {{ $order->fecha_entrega->format('d/m/Y H:i') }}
                                        @else
                                            —
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 font-medium">Inicio</dt>
                                    <dd>
                                        @if($order->fecha_inicio)
                                            {{ $order->fecha_inicio->format('d/m/Y H:i') }}
                                        @else
                                            —
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 font-medium">Recolección</dt>
                                    <dd>
                                        @if($order->fecha_recoleccion)
                                            {{ $order->fecha_recoleccion->format('d/m/Y H:i') }}
                                        @else
                                            —
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    {{-- Notas --}}
                    @if($order->notas)
                        <div class="pt-4 mt-4 border-t border-gray-200">
                            <h3 class="mb-2 text-sm font-semibold text-gray-600 uppercase">
                                Notas
                            </h3>
                            <p class="text-sm leading-relaxed text-gray-800 whitespace-pre-line">
                                {{ $order->notas }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Líneas de la orden --}}
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="mb-3 text-sm font-semibold text-gray-600 uppercase">
                        Detalle de la orden
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Tipo</th>
                                    <th class="px-4 py-2 text-left">Descripción</th>
                                    <th class="px-4 py-2 text-right">Cantidad</th>
                                    <th class="px-4 py-2 text-right">Precio unitario</th>
                                    <th class="px-4 py-2 text-right">Impuesto %</th>
                                    <th class="px-4 py-2 text-right">Subtotal</th>
                                    <th class="px-4 py-2 text-right">Impuesto</th>
                                    <th class="px-4 py-2 text-right">Total línea</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($order->lineas as $line)
                                    @php
                                        $tipoLabel = match($line->tipo) {
                                            'item'   => 'Ítem',
                                            'bundle' => 'Paquete',
                                            'extra'  => 'Extra',
                                            default  => ucfirst($line->tipo),
                                        };

                                        $descripcion = $line->descripcion
                                            ?? ($line->item->nombre ?? null)
                                            ?? ($line->bundle->nombre ?? null)
                                            ?? '—';
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-2">
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                                                {{ $tipoLabel }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="text-sm text-gray-900">
                                                {{ $descripcion }}
                                            </div>
                                            @if($line->item)
                                                <div class="text-xs text-gray-500">
                                                    SKU: {{ $line->item->sku }}
                                                </div>
                                            @elseif($line->bundle)
                                                <div class="text-xs text-gray-500">
                                                    Paquete SKU: {{ $line->bundle->sku }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            {{ $line->cantidad }}
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            ${{ number_format($line->precio_unitario, 2) }}
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            {{ number_format($line->impuesto_porcentaje, 2) }}%
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            ${{ number_format($line->subtotal, 2) }}
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            ${{ number_format($line->impuesto_monto, 2) }}
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            ${{ number_format($line->total, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-4 text-sm text-center text-gray-500">
                                            Esta orden aún no tiene líneas registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Totales --}}
                    <div class="flex justify-end mt-4">
                        <div class="w-full max-w-sm space-y-1 text-sm text-gray-700">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span>${{ number_format($order->subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Impuestos:</span>
                                <span>${{ number_format($order->impuestos, 2) }}</span>
                            </div>
                            <div class="pt-2 mt-2 text-base font-semibold border-t border-gray-200">
                                <div class="flex justify-between">
                                    <span>Total:</span>
                                    <span>${{ number_format($order->total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- (Opcional futuro) timeline de estatus, pagos, etc. --}}
        </div>
    </div>
</x-app-layout>
