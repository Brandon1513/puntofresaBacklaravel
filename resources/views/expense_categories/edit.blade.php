<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Editar categoría de gasto') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white rounded-lg shadow-sm">
                <div class="p-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Edición</h3>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('expense-categories.update', $cat) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <x-input-label for="nombre" value="Nombre" />
                            <x-text-input id="nombre" name="nombre" type="text"
                                          class="block w-full mt-1"
                                          :value="old('nombre', $cat->nombre)" required />
                            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-2 mb-4">
                            <input id="activo" name="activo" type="checkbox"
                                   class="text-indigo-600 border-gray-300 rounded shadow-sm"
                                   @checked(old('activo', $cat->activo))>
                            <label for="activo" class="text-sm text-gray-700">
                                Activa
                            </label>
                        </div>

                        <div class="flex justify-end gap-2 mt-6">
                            <a href="{{ route('expense-categories.index') }}"
                               class="px-3 py-2 text-sm text-gray-700 border rounded-md hover:bg-gray-50">
                                Cancelar
                            </a>

                            <x-primary-button>
                                Actualizar
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
