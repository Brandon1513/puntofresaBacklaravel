<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Catálogo de ítems
            </h2>
            <a href="{{ route('items.create') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                Nuevo ítem
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto space-y-4 sm:px-6 lg:px-8">

            {{-- Mensaje OK --}}
            @if (session('ok'))
                <div class="px-4 py-3 text-sm text-green-800 border border-green-200 rounded-md bg-green-50">
                    {{ session('ok') }}
                </div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    {{-- Filtros --}}
                    <div class="p-4 mb-4 border rounded-lg bg-gray-50">
                        <form method="GET" action="{{ route('items.index') }}"
                              class="grid items-end grid-cols-1 gap-4 md:grid-cols-4">
                            {{-- Buscar nombre / SKU --}}
                            <div>
                                <x-input-label for="search" value="Buscar" />
                                <x-text-input id="search" name="search" type="text"
                                              placeholder="Nombre o SKU"
                                              class="block w-full mt-1"
                                              value="{{ $filters['search'] ?? '' }}" />
                            </div>

                            {{-- Categoría --}}
                            <div>
                                <x-input-label for="categoria_id" value="Categoría" />
                                <select id="categoria_id" name="categoria_id"
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                    <option value="">Todas</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}"
                                            @selected(($filters['categoria_id'] ?? null) == $cat->id)>
                                            {{ $cat->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Estado --}}
                            <div>
                                <x-input-label for="estado" value="Estado" />
                                <select id="estado" name="estado"
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                    <option value="">Todos</option>
                                    <option value="activos" @selected(($filters['estado'] ?? '') === 'activos')>Activos</option>
                                    <option value="inactivos" @selected(($filters['estado'] ?? '') === 'inactivos')>Inactivos</option>
                                </select>
                            </div>

                            {{-- Botones --}}
                            <div class="flex items-end gap-2">
                                <x-primary-button class="justify-center flex-1">
                                    Filtrar
                                </x-primary-button>

                                <a href="{{ route('items.index') }}"
                                   class="inline-flex items-center px-3 py-2 text-sm text-gray-700 bg-white border rounded-md hover:bg-gray-50">
                                    Limpiar
                                </a>
                            </div>
                        </form>

                        {{-- Resumen + Exportar --}}
                        <div class="flex flex-col items-start justify-between mt-4 gap-y-2 md:flex-row md:items-center">
                            <p class="text-sm text-gray-500">
                                Resultados: <span class="font-semibold text-gray-700">{{ $items->total() }}</span>
                            </p>
                            <a href="{{ route('items.import.form') }}"
                            class="px-4 py-2 text-sm font-semibold text-indigo-600 bg-white border border-indigo-200 rounded-md hover:bg-indigo-50">
                                Importar Excel
                            </a>


                            <a href="{{ route('items.export', request()->query()) }}"
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-md bg-emerald-600 hover:bg-emerald-700">
                                Exportar Excel
                            </a>
                        </div>
                    </div>

                    {{-- Tabla --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-xs font-semibold text-left text-gray-500 uppercase">SKU</th>
                                    <th class="px-4 py-2 text-xs font-semibold text-left text-gray-500 uppercase">Nombre</th>
                                    <th class="px-4 py-2 text-xs font-semibold text-left text-gray-500 uppercase">Categoría</th>
                                    <th class="px-4 py-2 text-xs font-semibold text-left text-gray-500 uppercase">Stock</th>
                                    <th class="px-4 py-2 text-xs font-semibold text-left text-gray-500 uppercase">Estado</th>
                                    <th class="px-4 py-2 text-xs font-semibold text-right text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($items as $item)
                                    <tr class="transition hover:bg-gray-50">
                                        <td class="px-4 py-2 font-mono text-xs text-gray-700">
                                            {{ $item->sku }}
                                        </td>
                                        <td class="px-4 py-2">
                                            <a href="{{ route('items.show', $item) }}"
                                               class="text-sm font-medium text-indigo-700 hover:underline">
                                                {{ $item->nombre }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-700">
                                            {{ $item->categoria->nombre ?? '—' }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-800">
                                            {{ $item->stock_fisico }}
                                        </td>
                                        <td class="px-4 py-2">
                                            @if($item->activo)
                                                <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">
                                                    Activo
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">
                                                    Inactivo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <div class="flex items-center justify-end gap-3">
                                                <a href="{{ route('items.show', $item) }}"
                                                   class="text-xs text-indigo-600 hover:underline">
                                                    Ver
                                                </a>
                                                <a href="{{ route('items.edit', $item) }}"
                                                   class="text-xs text-gray-700 hover:underline">
                                                    Editar
                                                </a>
                                                <a href="{{ route('items.ajustes.index', $item) }}"
                                                   class="inline-flex items-center px-3 py-1 text-xs font-semibold text-white rounded-full bg-emerald-600 hover:bg-emerald-700">
                                                    Bitácora de inventario
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-sm text-center text-gray-500">
                                            No hay ítems que coincidan con los filtros.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $items->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
