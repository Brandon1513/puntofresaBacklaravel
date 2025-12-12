<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Clientes
            </h2>
            <a href="{{ route('clientes.create') }}"
               class="px-3 py-1 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                Nuevo cliente
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
                    <form method="GET" action="{{ route('clientes.index') }}"
                          class="grid items-end grid-cols-1 gap-4 mb-4 md:grid-cols-4">
                        <div class="md:col-span-2">
                            <x-input-label for="search" value="Buscar" />
                            <x-text-input id="search" name="search" type="text"
                                          placeholder="Nombre, email, teléfono o RFC"
                                          class="block w-full mt-1"
                                          value="{{ $filters['search'] ?? '' }}" />
                        </div>

                        <div>
                            <x-input-label for="estado" value="Estado" />
                            <select id="estado" name="estado"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                <option value="">Todos</option>
                                <option value="activos" @selected(($filters['estado'] ?? '') === 'activos')>Activos</option>
                                <option value="inactivos" @selected(($filters['estado'] ?? '') === 'inactivos')>Inactivos</option>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <x-primary-button class="flex-1">
                                Filtrar
                            </x-primary-button>

                            <a href="{{ route('clientes.index') }}"
                               class="px-3 py-2 text-sm text-gray-700 bg-gray-100 border rounded-md hover:bg-gray-200">
                                Limpiar
                            </a>
                        </div>
                    </form>

                    <div class="flex items-center justify-between mb-4">
                        <p class="text-sm text-gray-500">
                            Resultados: {{ $clientes->total() }}
                        </p>
                    </div>

                    {{-- Tabla --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Nombre</th>
                                    <th class="px-4 py-2 text-left">Contacto</th>
                                    <th class="px-4 py-2 text-left">Tipo</th>
                                    <th class="px-4 py-2 text-left">RFC</th>
                                    <th class="px-4 py-2 text-left">Estado</th>
                                    <th class="px-4 py-2 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($clientes as $cliente)
                                    <tr>
                                        <td class="px-4 py-2">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $cliente->nombre }}
                                            </div>
                                            @if($cliente->email)
                                                <div class="text-xs text-gray-500">
                                                    {{ $cliente->email }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            @if($cliente->telefono)
                                                <div class="text-sm text-gray-800">
                                                    {{ $cliente->telefono }}
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ $cliente->tipo ?: '—' }}
                                        </td>
                                        <td class="px-4 py-2 font-mono text-xs text-gray-700">
                                            {{ $cliente->rfc ?: '—' }}
                                        </td>
                                        <td class="px-4 py-2">
                                            @if($cliente->activo)
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
                                            <a href="{{ route('clientes.edit', $cliente) }}"
                                               class="text-xs text-indigo-600 hover:underline">
                                                Editar
                                            </a>

                                            <form action="{{ route('clientes.toggle', $cliente) }}"
                                                  method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="ml-2 text-xs text-gray-600 hover:underline"
                                                        onclick="return confirm('¿Cambiar estado del cliente?')">
                                                    {{ $cliente->activo ? 'Desactivar' : 'Activar' }}
                                                </button>
                                            </form>

                                            {{-- Más adelante: link a historial de órdenes --}}
                                            {{-- <a href="{{ route('clientes.ordenes', $cliente) }}" class="ml-2 text-xs text-sky-600 hover:underline">
                                                Historial de eventos
                                            </a> --}}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-4 text-sm text-center text-gray-500">
                                            No hay clientes que coincidan con los filtros.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $clientes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
