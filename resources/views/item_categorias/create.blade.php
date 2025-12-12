<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Nueva categoría de ítem') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white rounded shadow-sm">
                <div class="px-4 py-3 border-b">
                    <h3 class="text-base font-semibold text-gray-900">
                        Registrar categoría
                    </h3>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('item-categorias.store') }}">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="nombre" value="Nombre" />
                            <x-text-input id="nombre" name="nombre" type="text"
                                          class="block w-full mt-1"
                                          :value="old('nombre')" required />
                            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="descripcion" value="Descripción" />
                            <textarea id="descripcion" name="descripcion" rows="3"
                                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">{{ old('descripcion') }}</textarea>
                            <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                        </div>

                        <div class="flex items-center mb-4">
                            <input id="activo" name="activo" type="checkbox" value="1"
                                   class="w-4 h-4 border-gray-300 rounded"
                                   {{ old('activo', true) ? 'checked' : '' }}>
                            <label for="activo" class="ml-2 text-sm text-gray-700">Activa</label>
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="{{ route('item-categorias.index') }}"
                               class="px-3 py-2 text-sm text-gray-700 border rounded-md hover:bg-gray-50">
                                Cancelar
                            </a>
                            <x-primary-button>
                                Guardar
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
