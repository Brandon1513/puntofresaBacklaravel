@php
    /** @var \App\Models\Bundle|null $bundle */
    $isEdit = $bundle && $bundle->id;
@endphp

<div class="space-y-6">
    {{-- Datos generales --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <x-input-label for="sku" value="SKU del paquete" />
            <x-text-input id="sku" name="sku" type="text"
                          class="block w-full mt-1"
                          :value="old('sku', $bundle->sku ?? '')" required />
            <x-input-error :messages="$errors->get('sku')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="nombre" value="Nombre" />
            <x-text-input id="nombre" name="nombre" type="text"
                          class="block w-full mt-1"
                          :value="old('nombre', $bundle->nombre ?? '')" required />
            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
        </div>

        <div class="md:col-span-2">
            <x-input-label for="descripcion" value="Descripción" />
            <textarea id="descripcion" name="descripcion" rows="2"
                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">{{ old('descripcion', $bundle->descripcion ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
        </div>
    </div>

    {{-- Precio / descuento --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div>
            <x-input-label for="descuento_porcentaje" value="% descuento sobre suma de ítems" />
            <x-text-input id="descuento_porcentaje" name="descuento_porcentaje" type="number" min="0" max="100"
                          class="block w-full mt-1"
                          :value="old('descuento_porcentaje', $bundle->descuento_porcentaje ?? 0)" />
            <x-input-error :messages="$errors->get('descuento_porcentaje')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="precio_personalizado" value="Precio personalizado (opcional)" />
            <x-text-input id="precio_personalizado" name="precio_personalizado" type="number" step="0.01" min="0"
                          class="block w-full mt-1"
                          :value="old('precio_personalizado', $bundle->precio_personalizado ?? '')" />
            <x-input-error :messages="$errors->get('precio_personalizado')" class="mt-2" />
        </div>

        <div class="flex items-center mt-6 space-x-2">
            <input id="usar_precio_personalizado" name="usar_precio_personalizado" type="checkbox" value="1"
                   class="w-4 h-4 border-gray-300 rounded"
                   {{ old('usar_precio_personalizado', $bundle->usar_precico_personalizado ?? $bundle->usar_precio_personalizado ?? false) ? 'checked' : '' }}>
            <label for="usar_precio_personalizado" class="text-sm text-gray-700">
                Usar precio personalizado como precio final
            </label>
        </div>
    </div>

    <div class="flex items-center space-x-2">
        <input id="activo" name="activo" type="checkbox" value="1"
               class="w-4 h-4 border-gray-300 rounded"
               {{ old('activo', $bundle->activo ?? true) ? 'checked' : '' }}>
        <label for="activo" class="text-sm text-gray-700">
            Paquete activo
        </label>
    </div>

    {{-- Vigencia --}}
    <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-2">
        <div>
            <x-input-label for="vigente_desde" value="Vigente desde" />
            <x-text-input id="vigente_desde" type="date" name="vigente_desde"
                class="block w-full mt-1"
                :value="old('vigente_desde', optional($bundle ?? null)->vigente_desde?->format('Y-m-d'))" />
            <p class="mt-1 text-xs text-gray-500">
                Opcional. Si lo dejas vacío, empieza a aplicar desde hoy.
            </p>
            <x-input-error :messages="$errors->get('vigente_desde')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="vigente_hasta" value="Vigente hasta" />
            <x-text-input id="vigente_hasta" type="date" name="vigente_hasta"
                class="block w-full mt-1"
                :value="old('vigente_hasta', optional($bundle ?? null)->vigente_hasta?->format('Y-m-d'))" />
            <p class="mt-1 text-xs text-gray-500">
                Opcional. Si lo dejas vacío, no tiene fecha de fin.
            </p>
            <x-input-error :messages="$errors->get('vigente_hasta')" class="mt-2" />
        </div>
    </div>

    {{-- Fotos del paquete --}}
    <div class="pt-4 mt-4 border-t border-gray-200">
        <h3 class="mb-2 text-sm font-semibold text-gray-700 uppercase">
            Fotos del paquete
        </h3>

        <p class="mb-2 text-xs text-gray-500">
            Puedes subir una o varias imágenes. La primera foto se usará como principal
            (portada del combo en el catálogo).
        </p>

        <input type="file"
               name="photos[]"
               multiple
               accept="image/*"
               class="block w-full text-sm text-gray-700 border-gray-300 rounded-md shadow-sm">

        <x-input-error :messages="$errors->get('photos')" class="mt-2" />
        <x-input-error :messages="$errors->get('photos.*')" class="mt-2" />

        @if($isEdit && $bundle->relationLoaded('photos') && $bundle->photos->count())
            <div class="grid grid-cols-2 gap-3 mt-4 sm:grid-cols-3 md:grid-cols-4">
                @foreach($bundle->photos as $photo)
                    <div class="p-1 bg-white border rounded">
                        <img src="{{ Storage::disk('public')->url($photo->path) }}"
                             alt="Foto paquete"
                             class="object-cover w-full h-24 rounded">
                        @if($photo->es_principal)
                            <p class="mt-1 text-xs font-semibold text-green-600">
                                Principal
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Líneas del paquete --}}
    <div class="pt-4 mt-4 border-t border-gray-200">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-700 uppercase">
                Ítems del paquete
            </h3>

            <button type="button" id="btn-add-line"
                    class="inline-flex items-center px-3 py-1 text-xs font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                + Agregar ítem
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Ítem</th>
                        <th class="px-3 py-2 text-left w-28">Cantidad</th>
                        <th class="w-40 px-3 py-2 text-left">Precio especial (opcional)</th>
                        <th class="w-24 px-3 py-2 text-right"></th>
                    </tr>
                </thead>
                <tbody id="bundle-lines-body" class="divide-y divide-gray-200">
                    @php
                        $oldItems  = old('line_items');
                        $oldQty    = old('line_qty');
                        $oldPrice  = old('line_price');
                        $hasOld    = is_array($oldItems) && count($oldItems) > 0;

                        $lines = $hasOld
                            ? collect($oldItems)->map(function ($itemId, $idx) use ($oldQty, $oldPrice) {
                                return [
                                    'item_id'  => $itemId,
                                    'cantidad' => $oldQty[$idx]   ?? 1,
                                    'precio'   => $oldPrice[$idx] ?? null,
                                ];
                              })
                            : ($bundle->relationLoaded('lineas') ? $bundle->lineas : collect());
                    @endphp

                    @forelse($lines as $line)
                        <tr>
                            {{-- Ítem --}}
                            <td class="px-3 py-2">
                                <select name="line_items[]" class="block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">— Selecciona —</option>
                                    @foreach($items as $it)
                                        <option value="{{ $it->id }}"
                                            @selected(($line['item_id'] ?? $line->item_id) == $it->id)>
                                            {{ $it->nombre }} ({{ $it->sku }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Cantidad --}}
                            <td class="px-3 py-2">
                                <x-text-input name="line_qty[]" type="number" min="1"
                                              class="block w-full"
                                              :value="$line['cantidad'] ?? $line->cantidad ?? 1" />
                            </td>

                            {{-- Precio especial --}}
                            <td class="px-3 py-2">
                                <x-text-input name="line_price[]" type="number" step="0.01" min="0"
                                              class="block w-full"
                                              :value="$line['precio'] ?? $line->precio_unitario_paquete ?? ''" />
                                <p class="mt-1 text-[11px] text-gray-400">
                                    Si lo dejas vacío, usa el precio normal del ítem.
                                </p>
                            </td>

                            {{-- Acción --}}
                            <td class="px-3 py-2 text-right">
                                <button type="button"
                                        class="text-xs text-red-600 hover:underline btn-remove-line">
                                    Quitar
                                </button>
                            </td>
                        </tr>
                    @empty
                        {{-- fila vacía inicial --}}
                        <tr>
                            <td class="px-3 py-2">
                                <select name="line_items[]" class="block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">— Selecciona —</option>
                                    @foreach($items as $it)
                                        <option value="{{ $it->id }}">
                                            {{ $it->nombre }} ({{ $it->sku }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2">
                                <x-text-input name="line_qty[]" type="number" min="1"
                                              class="block w-full" value="1" />
                            </td>
                            <td class="px-3 py-2">
                                <x-text-input name="line_price[]" type="number" step="0.01" min="0"
                                              class="block w-full" />
                                <p class="mt-1 text-[11px] text-gray-400">
                                    Si lo dejas vacío, usa el precio normal del ítem.
                                </p>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <button type="button"
                                        class="text-xs text-red-600 hover:underline btn-remove-line">
                                    Quitar
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <x-input-error :messages="$errors->get('line_items')" class="mt-2" />
        </div>
    </div>

    {{-- Acciones --}}
    <div class="flex items-center justify-end mt-6 space-x-3">
        <a href="{{ route('bundles.index') }}"
           class="inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
            Cancelar
        </a>

        <x-primary-button>
            {{ $isEdit ? 'Actualizar paquete' : 'Guardar paquete' }}
        </x-primary-button>
    </div>
</div>

{{-- Script para filas dinámicas --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const body   = document.getElementById('bundle-lines-body');
        const btnAdd = document.getElementById('btn-add-line');

        if (!body || !btnAdd) return;

        const template = () => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-3 py-2">
                    <select name="line_items[]" class="block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">— Selecciona —</option>
                        @foreach($items as $it)
                            <option value="{{ $it->id }}">
                                {{ $it->nombre }} ({{ $it->sku }})
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="number" name="line_qty[]" min="1" value="1"
                           class="block w-full border-gray-300 rounded-md shadow-sm" />
                </td>
                <td class="px-3 py-2">
                    <input type="number" name="line_price[]" step="0.01" min="0"
                           class="block w-full border-gray-300 rounded-md shadow-sm" />
                    <p class="mt-1 text-[11px] text-gray-400">
                        Vacío = precio normal del ítem.
                    </p>
                </td>
                <td class="px-3 py-2 text-right">
                    <button type="button"
                            class="text-xs text-red-600 hover:underline btn-remove-line">
                        Quitar
                    </button>
                </td>
            `;
            return tr;
        };

        btnAdd.addEventListener('click', () => {
            body.appendChild(template());
        });

        body.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-remove-line')) {
                const rows = body.querySelectorAll('tr');
                if (rows.length <= 1) {
                    return; // deja al menos una fila
                }
                e.target.closest('tr').remove();
            }
        });
    });
</script>
