@php($isEdit = isset($item) && $item?->id) 

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    {{-- SKU --}}
    <div>
        <x-input-label for="sku" value="SKU" />
        <x-text-input id="sku" name="sku" type="text"
                      class="block w-full mt-1"
                      :value="old('sku', $item->sku ?? '')" required />
        <x-input-error :messages="$errors->get('sku')" class="mt-2" />
    </div>

    {{-- Nombre --}}
    <div>
        <x-input-label for="nombre" value="Nombre" />
        <x-text-input id="nombre" name="nombre" type="text"
                      class="block w-full mt-1"
                      :value="old('nombre', $item->nombre ?? '')" required />
        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    {{-- Categoría --}}
    <div>
        <x-input-label for="categoria_id" value="Categoría" />
        <select id="categoria_id" name="categoria_id"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm" required>
            <option value="">— Selecciona —</option>
            @foreach($categorias as $c)
                <option value="{{ $c->id }}"
                    @selected(old('categoria_id', $item->categoria_id ?? null) == $c->id)>
                    {{ $c->nombre }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('categoria_id')" class="mt-2" />
    </div>

    {{-- Unidad --}}
    <div>
        <x-input-label for="unidad_id" value="Unidad" />
        <select id="unidad_id" name="unidad_id"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm" required>
            <option value="">— Selecciona —</option>
            @foreach($unidades as $u)
                <option value="{{ $u->id }}"
                    @selected(old('unidad_id', $item->unidad_id ?? null) == $u->id)>
                    {{ $u->nombre }} ({{ $u->abreviatura }})
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('unidad_id')" class="mt-2" />
    </div>

    {{-- Precios --}}
    <div>
        <x-input-label for="precio_renta_dia" value="Precio renta (día)" />
        <x-text-input id="precio_renta_dia" name="precio_renta_dia" type="number" step="0.01"
                      class="block w-full mt-1"
                      :value="old('precio_renta_dia', $item->precio_renta_dia ?? '')" />
        <x-input-error :messages="$errors->get('precio_renta_dia')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="precio_renta_fin" value="Precio renta (fin de semana)" />
        <x-text-input id="precio_renta_fin" name="precio_renta_fin" type="number" step="0.01"
                      class="block w-full mt-1"
                      :value="old('precio_renta_fin', $item->precio_renta_fin ?? '')" />
        <x-input-error :messages="$errors->get('precio_renta_fin')" class="mt-2" />
    </div>

    {{-- Costos --}}
    <div>
        <x-input-label for="costo_promedio" value="Costo promedio" />
        <x-text-input id="costo_promedio" name="costo_promedio" type="number" step="0.01"
                      class="block w-full mt-1"
                      :value="old('costo_promedio', $item->costo_promedio ?? '')" />
        <x-input-error :messages="$errors->get('costo_promedio')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="costo_reposicion" value="Costo de reposición" />
        <x-text-input id="costo_reposicion" name="costo_reposicion" type="number" step="0.01"
                      class="block w-full mt-1"
                      :value="old('costo_reposicion', $item->costo_reposicion ?? '')" />
        <x-input-error :messages="$errors->get('costo_reposicion')" class="mt-2" />
    </div>

    {{-- Stock --}}
    <div>
        <x-input-label for="stock_fisico" value="Stock físico" />
        <x-text-input id="stock_fisico" name="stock_fisico" type="number" min="0"
                      class="block w-full mt-1"
                      :value="old('stock_fisico', $item->stock_fisico ?? 0)" />
        <x-input-error :messages="$errors->get('stock_fisico')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="stock_minimo" value="Stock mínimo" />
        <x-text-input id="stock_minimo" name="stock_minimo" type="number" min="0"
                      class="block w-full mt-1"
                      :value="old('stock_minimo', $item->stock_minimo ?? 0)" />
        <x-input-error :messages="$errors->get('stock_minimo')" class="mt-2" />
    </div>

    {{-- Ubicación --}}
    <div class="md:col-span-2">
        <x-input-label for="ubicacion" value="Ubicación" />
        <x-text-input id="ubicacion" name="ubicacion" type="text"
                      class="block w-full mt-1"
                      :value="old('ubicacion', $item->ubicacion ?? '')" />
        <x-input-error :messages="$errors->get('ubicacion')" class="mt-2" />
    </div>

    {{-- Tags --}}
    <div class="md:col-span-2">
        <x-input-label for="tags" value="Tags (separados por comas)" />
        <x-text-input id="tags" name="tags" type="text"
                      class="block w-full mt-1"
                      :value="old('tags', $item->tags ?? '')" />
        <x-input-error :messages="$errors->get('tags')" class="mt-2" />
    </div>

    {{-- Descripción (NUEVO) --}}
    <div class="md:col-span-2">
        <x-input-label for="descripcion" value="Descripción" />
        <textarea id="descripcion" name="descripcion" rows="3"
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
{{ old('descripcion', $item->descripcion ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
    </div>

    {{-- Fotos (NUEVO) --}}
    <div class="md:col-span-2">
        <x-input-label for="photos" value="Fotos del ítem" />
        <input id="photos" name="photos[]" type="file" multiple accept="image/*"
            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm" />
        <p class="mt-1 text-xs text-gray-500">
            Puedes subir varias fotos (jpg, png, máx. 2MB cada una).
        </p>

        {{-- Mostrar errores de photos.* sin usar x-input-error --}}
        @if ($errors->has('photos.*'))
            <ul class="mt-2 space-y-1 text-sm text-red-600">
                @foreach ($errors->get('photos.*') as $messages)
                    @foreach ((array) $messages as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                @endforeach
            </ul>
        @endif
    </div>


    {{-- (Opcional) mini-galería cuando editas --}}
    @if($isEdit && isset($item) && $item->photos?->count())
        <div class="md:col-span-2">
            <p class="text-sm font-semibold text-gray-700">Fotos actuales:</p>
            <div class="flex flex-wrap gap-3 mt-2">
                @foreach($item->photos as $photo)
                    <div>
                        <img src="{{ asset('storage/'.$photo->path) }}"
                             class="object-cover w-24 h-24 border rounded-md">
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Activo --}}
    <div class="flex items-center md:col-span-2">
        <input id="activo" name="activo" type="checkbox" value="1"
               class="w-4 h-4 border-gray-300 rounded"
               {{ old('activo', $item->activo ?? true) ? 'checked' : '' }}>
        <label for="activo" class="ml-2 text-sm text-gray-700">
            Ítem activo
        </label>
    </div>
</div>

<div class="flex justify-end gap-2 mt-6">
    <a href="{{ route('items.index') }}"
       class="px-3 py-2 text-sm text-gray-700 border rounded-md hover:bg-gray-50">
        Cancelar
    </a>
    <x-primary-button>
        {{ $isEdit ? 'Actualizar ítem' : 'Guardar ítem' }}
    </x-primary-button>
</div>
