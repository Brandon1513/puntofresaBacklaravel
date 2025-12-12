@php($isEdit = isset($expense) && $expense?->id)

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    {{-- Fecha --}}
    <div>
        <x-input-label for="fecha" value="Fecha" />
        <x-text-input id="fecha" name="fecha" type="date"
            class="block w-full mt-1"
            :value="old('fecha', isset($expense) ? $expense->fecha->format('Y-m-d') : now()->format('Y-m-d'))"
            required />
        <x-input-error :messages="$errors->get('fecha')" class="mt-2" />
    </div>

    {{-- Monto --}}
    <div>
        <x-input-label for="monto" value="Monto" />
        <x-text-input id="monto" name="monto" type="number" step="0.01"
            class="block w-full mt-1"
            :value="old('monto', $expense->monto ?? '')"
            required placeholder="Puedes utilizar decimales" />
        <x-input-error :messages="$errors->get('monto')" class="mt-2" />
    </div>

    {{-- Categoría --}}
    <div>
        <x-input-label for="expense_category_id" value="Categoría" />
        <select id="expense_category_id" name="expense_category_id"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
            <option value="">— Selecciona —</option>
            @foreach ($cats as $c)
                <option value="{{ $c->id }}"
                    @selected(old('expense_category_id', $expense->expense_category_id ?? null) == $c->id)>
                    {{ $c->nombre }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('expense_category_id')" class="mt-2" />
    </div>

    {{-- Centro de costo --}}
    <div>
        <x-input-label for="cost_center_id" value="Centro de costo" />
        <select id="cost_center_id" name="cost_center_id"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
            <option value="">— Selecciona —</option>
            @foreach ($ccs as $c)
                <option value="{{ $c->id }}"
                    @selected(old('cost_center_id', $expense->cost_center_id ?? null) == $c->id)>
                    {{ $c->nombre }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('cost_center_id')" class="mt-2" />
    </div>

    {{-- Proveedor --}}
    <div class="md:col-span-2">
        <x-input-label for="proveedor" value="Proveedor" />
        <x-text-input id="proveedor" name="proveedor" type="text"
            class="block w-full mt-1"
            :value="old('proveedor', $expense->proveedor ?? '')" 
            placeholder="Descripción del gasto o titulo del proveedor como: Gasolina etc"/>
        <x-input-error :messages="$errors->get('proveedor')" class="mt-2" />
    </div>

    {{-- Método de pago --}}
    <div>
        <x-input-label for="metodo_pago" value="Método de pago" />
        <x-text-input id="metodo_pago" name="metodo_pago" type="text"
            class="block w-full mt-1"
            :value="old('metodo_pago', $expense->metodo_pago ?? '')"
            placeholder="Efectivo, transferencia..." />
        <x-input-error :messages="$errors->get('metodo_pago')" class="mt-2" />
    </div>

    {{-- Referencia --}}
    <div>
        <x-input-label for="referencia" value="Referencia" />
        <x-text-input id="referencia" name="referencia" type="text"
            class="block w-full mt-1"
            :value="old('referencia', $expense->referencia ?? '')"
            placeholder="Utiliza el folio del ticket" />
        <x-input-error :messages="$errors->get('referencia')" class="mt-2" />
    </div>

    {{-- Notas --}}
    <div class="md:col-span-2">
        <x-input-label for="notas" value="Notas" />
        <textarea id="notas" name="notas" rows="3"
            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">{{ old('notas', $expense->notas ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notas')" class="mt-2" />
    </div>

    {{-- Comprobantes --}}
    <div class="md:col-span-2">
        <x-input-label for="files" value="Comprobantes (PDF/imagen)" />
        <input id="files" name="files[]" type="file" multiple
            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm" />
        <x-input-error :messages="$errors->get('files')" class="mt-2" />

        {{-- Solo en edición, mostrar cuántos adjuntos hay --}}
        @if ($isEdit && $expense->attachments?->count())
            <div class="mt-2 text-sm text-gray-600">
                Adjuntos actuales: {{ $expense->attachments->count() }} archivo(s)
                — puedes verlos en el detalle del gasto.
            </div>
        @endif
    </div>
</div>

{{-- Botones --}}
<div class="flex items-center justify-end gap-2 mt-6">
    <a href="{{ route('expenses.index') }}"
       class="px-3 py-2 text-sm text-gray-700 border rounded-md hover:bg-gray-50">
        Cancelar
    </a>

    <x-primary-button>
        {{ $isEdit ? 'Actualizar' : 'Guardar gasto' }}
    </x-primary-button>
</div>
