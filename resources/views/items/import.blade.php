<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Importar items desde Excel
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <p class="mb-2 text-sm text-gray-600">
                        Sube un archivo Excel con los encabezados:
                        <span class="font-mono text-xs">
                            sku, nombre, categoria, unidad, precio_renta_dia,
                            precio_renta_fin, costo_promedio, costo_reposicion,
                            stock_fisico, stock_minimo, ubicacion, activo, tags, descripcion
                        </span>
                    </p>

                    <p class="mb-4 text-sm text-gray-600">
                        ¿Tienes dudas del formato? 
                        <a href="{{ route('items.import.template') }}" class="font-semibold text-indigo-600 hover:underline">
                            Descarga un archivo de ejemplo
                        </a>
                        y úsalo como base.
                    </p>

                    @if (session('error'))
                        <div class="p-3 mb-4 text-sm text-red-700 bg-red-100 border border-red-200 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session('ok'))
                        <div class="p-3 mb-4 text-sm text-green-700 bg-green-100 border border-green-200 rounded">
                            {{ session('ok') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('items.import') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <div>
                            <x-input-label for="file" value="Archivo Excel" />
                            <input id="file" name="file" type="file"
                                   class="block w-full mt-1 text-sm text-gray-700 border-gray-300 rounded-md shadow-sm"
                                   accept=".xlsx,.xls,.csv">
                            <x-input-error :messages="$errors->get('file')" class="mt-2" />
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('items.index') }}"
                               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                Cancelar
                            </a>
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                                Importar
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
