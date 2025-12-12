@php
    $isEdit = isset($cliente) && $cliente?->id;
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <x-input-label for="nombre" value="Nombre del cliente" />
            <x-text-input id="nombre" name="nombre" type="text"
                          class="block w-full mt-1"
                          :value="old('nombre', $cliente->nombre ?? '')" required />
            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" name="email" type="email"
                          class="block w-full mt-1"
                          :value="old('email', $cliente->email ?? '')" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="telefono" value="Teléfono" />
            <x-text-input id="telefono" name="telefono" type="text"
                          class="block w-full mt-1"
                          :value="old('telefono', $cliente->telefono ?? '')" />
            <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="tipo" value="Tipo de cliente" />
            <x-text-input id="tipo" name="tipo" type="text"
                          placeholder="Persona, empresa, salón, etc."
                          class="block w-full mt-1"
                          :value="old('tipo', $cliente->tipo ?? '')" />
            <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="rfc" value="RFC" />
            <x-text-input id="rfc" name="rfc" type="text"
                          class="block w-full mt-1"
                          :value="old('rfc', $cliente->rfc ?? '')" />
            <x-input-error :messages="$errors->get('rfc')" class="mt-2" />
        </div>

        <div class="flex items-center mt-6 space-x-2">
            <input id="activo" name="activo" type="checkbox" value="1"
                   class="w-4 h-4 border-gray-300 rounded"
                   {{ old('activo', $cliente->activo ?? true) ? 'checked' : '' }}>
            <label for="activo" class="text-sm text-gray-700">
                Cliente activo
            </label>
        </div>
    </div>

    <div>
        <x-input-label for="notas" value="Notas" />
        <textarea id="notas" name="notas" rows="3"
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">{{ old('notas', $cliente->notas ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notas')" class="mt-2" />
    </div>

    <div class="flex justify-end gap-2 pt-4 mt-4 border-t border-gray-200">
        <a href="{{ route('clientes.index') }}"
           class="px-3 py-2 text-sm text-gray-700 bg-gray-100 border rounded-md hover:bg-gray-200">
            Cancelar
        </a>
        <x-primary-button>
            {{ $isEdit ? 'Actualizar cliente' : 'Guardar cliente' }}
        </x-primary-button>
    </div>
</div>
