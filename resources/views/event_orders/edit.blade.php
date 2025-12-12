<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editar orden de evento {{ $order->folio }}
            </h2>

            <a href="{{ route('event-orders.show', $order) }}"
               class="text-sm text-indigo-600 hover:text-indigo-800">
                ← Volver al detalle
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto space-y-6 sm:px-6 lg:px-8">

            {{-- FORMULARIO PRINCIPAL --}}
            <div class="max-w-4xl mx-auto overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">
                        Edición de orden
                    </h3>

                    @if ($errors->any())
                        <div class="mb-4 text-sm text-red-700 border border-red-200 rounded bg-red-50">
                            <ul class="px-3 py-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('event_orders.update', $order) }}">
                        @csrf
                        @method('PUT')

                        {{-- Datos del cliente --}}
                        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <h4 class="mb-2 text-sm font-semibold text-gray-600 uppercase">
                                    Datos del cliente
                                </h4>
                            </div>

                            <div>
                                <x-input-label for="cliente_nombre" value="Nombre del cliente" />
                                <x-text-input id="cliente_nombre" name="cliente_nombre" type="text"
                                              class="block w-full mt-1"
                                              :value="old('cliente_nombre', $order->cliente_nombre)" required />
                                <x-input-error :messages="$errors->get('cliente_nombre')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="cliente_email" value="Correo" />
                                <x-text-input id="cliente_email" name="cliente_email" type="email"
                                              class="block w-full mt-1"
                                              :value="old('cliente_email', $order->cliente_email)" />
                                <x-input-error :messages="$errors->get('cliente_email')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="cliente_telefono" value="Teléfono" />
                                <x-text-input id="cliente_telefono" name="cliente_telefono" type="text"
                                              class="block w-full mt-1"
                                              :value="old('cliente_telefono', $order->cliente_telefono)" />
                                <x-input-error :messages="$errors->get('cliente_telefono')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Información del evento --}}
                        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
                            <div class="md:col-span-3">
                                <h4 class="mb-2 text-sm font-semibold text-gray-600 uppercase">
                                    Información del evento
                                </h4>
                            </div>

                            <div>
                                <x-input-label for="fecha_inicio" value="Fecha de inicio" />
                                <x-text-input id="fecha_inicio" name="fecha_inicio" type="date"
                                              class="block w-full mt-1"
                                              :value="old('fecha_inicio', optional($order->fecha_inicio)->format('Y-m-d'))" />
                                <x-input-error :messages="$errors->get('fecha_inicio')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="fecha_entrega" value="Fecha de entrega" />
                                <x-text-input id="fecha_entrega" name="fecha_entrega" type="date"
                                              class="block w-full mt-1"
                                              :value="old('fecha_entrega', optional($order->fecha_entrega)->format('Y-m-d'))" />
                                <x-input-error :messages="$errors->get('fecha_entrega')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="fecha_recoleccion" value="Fecha de recolección" />
                                <x-text-input id="fecha_recoleccion" name="fecha_recoleccion" type="date"
                                              class="block w-full mt-1"
                                              :value="old('fecha_recoleccion', optional($order->fecha_recoleccion)->format('Y-m-d'))" />
                                <x-input-error :messages="$errors->get('fecha_recoleccion')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="lugar" value="Lugar" />
                                <x-text-input id="lugar" name="lugar" type="text"
                                              class="block w-full mt-1"
                                              :value="old('lugar', $order->lugar)" />
                                <x-input-error :messages="$errors->get('lugar')" class="mt-2" />
                            </div>

                            <div class="md:col-span-3">
                                <x-input-label for="direccion" value="Dirección" />
                                <x-text-input id="direccion" name="direccion" type="text"
                                              class="block w-full mt-1"
                                              :value="old('direccion', $order->direccion)" />
                                <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
                            </div>

                            <div class="md:col-span-3">
                                <x-input-label for="notas" value="Notas" />
                                <textarea id="notas" name="notas"
                                          class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                          rows="3">{{ old('notas', $order->notas) }}</textarea>
                                <x-input-error :messages="$errors->get('notas')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Estatus --}}
                        <div class="mb-2">
                            <h4 class="mb-2 text-sm font-semibold text-gray-600 uppercase">
                                Estatus
                            </h4>

                            <x-input-label for="estatus" value="Estatus de la orden" />
                            <select id="estatus" name="estatus"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach($estatusDisponibles as $key => $label)
                                    <option value="{{ $key }}"
                                        @selected(old('estatus', $order->estatus) === $key)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Desde esta vista solo administración puede modificar el estatus, incluso si la orden está en
                                <span class="font-semibold">Cierre</span>.
                            </p>
                        </div>

                        <div class="flex justify-end gap-2 mt-6">
                            <a href="{{ route('event-orders.show', $order) }}"
                               class="px-3 py-2 text-sm text-gray-700 border rounded-md hover:bg-gray-50">
                                Cancelar
                            </a>

                            <x-primary-button>
                                ACTUALIZAR ORDEN
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- DETALLE DE LÍNEAS (editable por admin) --}}
<div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <h3 class="mb-3 text-sm font-semibold text-gray-600 uppercase">
            Detalle de la orden
        </h3>

        <p class="mb-3 text-xs text-gray-500">
            Desde esta vista solo administración puede ajustar cantidades, precios e impuestos de cada línea.
        </p>

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

                            $oldCant   = old('lines.'.$line->id.'.cantidad', $line->cantidad);
                            $oldPrecio = old('lines.'.$line->id.'.precio_unitario', $line->precio_unitario);
                            $oldImp    = old('lines.'.$line->id.'.impuesto_porcentaje', $line->impuesto_porcentaje);
                            $oldDesc   = old('lines.'.$line->id.'.descripcion', $descripcion);
                        @endphp
                        <tr>
                            {{-- Tipo --}}
                            <td class="px-4 py-2 align-top">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                                    {{ $tipoLabel }}
                                </span>
                            </td>

                            {{-- Descripción editable --}}
                            <td class="px-4 py-2 align-top">
                                <input type="hidden"
                                       name="lines[{{ $line->id }}][id]"
                                       value="{{ $line->id }}">
                                <textarea
                                    name="lines[{{ $line->id }}][descripcion]"
                                    rows="2"
                                    class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >{{ $oldDesc }}</textarea>

                                @if($line->item)
                                    <div class="mt-1 text-xs text-gray-500">
                                        SKU: {{ $line->item->sku }}
                                    </div>
                                @elseif($line->bundle)
                                    <div class="mt-1 text-xs text-gray-500">
                                        Paquete SKU: {{ $line->bundle->sku }}
                                    </div>
                                @endif
                            </td>

                            {{-- Cantidad editable --}}
                            <td class="px-4 py-2 text-right align-top">
                                <input
                                    type="number"
                                    name="lines[{{ $line->id }}][cantidad]"
                                    value="{{ $oldCant }}"
                                    min="0"
                                    class="w-20 text-right border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </td>

                            {{-- Precio unitario editable --}}
                            <td class="px-4 py-2 text-right align-top">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="lines[{{ $line->id }}][precio_unitario]"
                                    value="{{ $oldPrecio }}"
                                    class="w-24 text-right border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </td>

                            {{-- % impuesto editable --}}
                            <td class="px-4 py-2 text-right align-top">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="lines[{{ $line->id }}][impuesto_porcentaje]"
                                    value="{{ $oldImp }}"
                                    class="w-20 text-right border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </td>

                            {{-- Subtotal / Impuesto / Total solo lectura (se recalculan al guardar) --}}
                            <td class="px-4 py-2 text-right align-top">
                                ${{ number_format($line->subtotal, 2) }}
                            </td>
                            <td class="px-4 py-2 text-right align-top">
                                ${{ number_format($line->impuesto_monto, 2) }}
                            </td>
                            <td class="px-4 py-2 text-right align-top">
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


        </div>
    </div>
</x-app-layout>
