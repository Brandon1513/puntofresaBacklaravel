<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Paquetes (Bundles)
            </h2>
            <a href="{{ route('bundles.create') }}"
               class="px-3 py-1 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                Nuevo paquete
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @if(session('ok'))
                        <div class="px-3 py-2 mb-4 text-sm text-green-800 bg-green-100 border border-green-200 rounded-md">
                            {{ session('ok') }}
                        </div>
                    @endif

                    {{-- Filtros --}}
                    <form method="GET" action="{{ route('bundles.index') }}"
                          class="grid items-end grid-cols-1 gap-4 mb-4 md:grid-cols-4">
                        <div>
                            <x-input-label for="search" value="Buscar" />
                            <x-text-input id="search" name="search" type="text"
                                          placeholder="SKU o nombre"
                                          class="block w-full mt-1"
                                          value="{{ $filters['search'] ?? '' }}" />
                        </div>

                        <div>
                            <x-input-label for="estado" value="Estado" />
                            <select id="estado" name="estado"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                <option value="">Todos</option>
                                <option value="activos"   @selected(($filters['estado'] ?? '') === 'activos')>Activos</option>
                                <option value="inactivos" @selected(($filters['estado'] ?? '') === 'inactivos')>Inactivos</option>
                                <option value="vigentes"  @selected(($filters['estado'] ?? '') === 'vigentes')>
                                    Vigentes hoy
                                </option>
                            </select>
                        </div>

                        <div class="flex gap-2 md:col-span-2">
                            <x-primary-button class="flex-1">
                                Filtrar
                            </x-primary-button>

                            <a href="{{ route('bundles.index') }}"
                               class="px-3 py-2 text-sm text-gray-700 bg-gray-100 border rounded-md hover:bg-gray-200">
                                Limpiar
                            </a>
                        </div>
                    </form>

                    <div class="flex items-center justify-between mb-4">
                        <p class="text-sm text-gray-500">
                            Resultados: {{ $bundles->total() }}
                        </p>
                    </div>

                    {{-- Tabla --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">SKU</th>
                                    <th class="px-4 py-2 text-left">Nombre</th>
                                    <th class="px-4 py-2 text-left">Vigencia</th>
                                    <th class="px-4 py-2 text-left">Líneas</th>
                                    <th class="px-4 py-2 text-left">Precio calculado</th>
                                    <th class="px-4 py-2 text-left">Precio final</th>
                                    <th class="px-4 py-2 text-left">Ahorro</th>
                                    <th class="px-4 py-2 text-left">Estado</th>
                                    <th class="px-4 py-2 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($bundles as $bundle)
                                    @php
                                        $desde = $bundle->vigente_desde;
                                        $hasta = $bundle->vigente_hasta;
                                        $hoy   = \Carbon\Carbon::today();

                                        $vigenteAhora = $bundle->activo &&
                                            (($desde === null || $desde->lte($hoy)) &&
                                             ($hasta === null || $hasta->gte($hoy)));

                                        $ahorro = $bundle->ahorro;
                                    @endphp

                                    <tr>
                                        {{-- SKU --}}
                                        <td class="px-4 py-2 font-mono text-xs text-gray-700">
                                            {{ $bundle->sku }}
                                        </td>

                                        {{-- Nombre + descripción --}}
                                        <td class="px-4 py-2">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $bundle->nombre }}
                                            </div>
                                            @if($bundle->descripcion)
                                                <div class="text-xs text-gray-500 line-clamp-1">
                                                    {{ $bundle->descripcion }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Vigencia --}}
                                        <td class="px-4 py-2">
                                            <div class="text-xs text-gray-700">
                                                @if(!$desde && !$hasta)
                                                    <span class="text-gray-500">Sin límite (siempre)</span>
                                                @else
                                                    <span>
                                                        {{ $desde ? $desde->format('d/m/Y') : '—' }}
                                                        &rarr;
                                                        {{ $hasta ? $hasta->format('d/m/Y') : '—' }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="mt-1">
                                                @if($vigenteAhora)
                                                    <span class="px-2 py-0.5 text-[11px] font-semibold text-emerald-700 bg-emerald-50 rounded-full">
                                                        Vigente hoy
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 text-[11px] text-gray-600 bg-gray-100 rounded-full text-[11px]">
                                                        Fuera de vigencia
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Líneas --}}
                                        <td class="px-4 py-2">
                                            {{ $bundle->lineas_count }}
                                        </td>

                                        {{-- Precio calculado --}}
                                        <td class="px-4 py-2">
                                            ${{ number_format($bundle->precio_calculado, 2) }}
                                        </td>

                                        {{-- Precio final --}}
                                        <td class="px-4 py-2">
                                            ${{ number_format($bundle->precio_final, 2) }}
                                        </td>

                                        {{-- Ahorro --}}
                                        <td class="px-4 py-2">
                                            @if($ahorro > 0)
                                                <span class="text-sm font-semibold text-emerald-700">
                                                    ${{ number_format($ahorro, 2) }}
                                                </span>
                                                <div class="text-[11px] text-emerald-600">
                                                    vs precio normal
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400">—</span>
                                            @endif
                                        </td>

                                        {{-- Estado activo/inactivo --}}
                                        <td class="px-4 py-2">
                                            @if($bundle->activo)
                                                <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">
                                                    Activo
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">
                                                    Inactivo
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Acciones --}}
                                        <td class="px-4 py-2 space-x-2 text-right">
                                            <a href="{{ route('bundles.edit', $bundle) }}"
                                               class="text-xs text-indigo-600 hover:underline">
                                                Editar
                                            </a>

                                            <form action="{{ route('bundles.toggle', $bundle) }}"
                                                  method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="text-xs text-gray-600 hover:underline"
                                                        onclick="return confirm('¿Cambiar estado del paquete?')">
                                                    {{ $bundle->activo ? 'Desactivar' : 'Activar' }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-4 py-4 text-sm text-center text-gray-500">
                                            No hay paquetes que coincidan con los filtros.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $bundles->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
