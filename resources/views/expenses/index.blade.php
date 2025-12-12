<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Gastos') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('ok'))
                <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-700">
                    {{ session('ok') }}
                </div>
            @endif

            {{-- Filtros --}}
            <div class="mb-4 rounded-lg bg-white p-6 shadow-sm">
                <form method="GET" class="flex flex-wrap items-end gap-4">
                    <div>
                        <x-input-label for="d1" value="Desde" />
                        <x-text-input id="d1" name="d1" type="date"
                                      class="mt-1 block w-full"
                                      :value="request('d1')" />
                    </div>

                    <div>
                        <x-input-label for="d2" value="Hasta" />
                        <x-text-input id="d2" name="d2" type="date"
                                      class="mt-1 block w-full"
                                      :value="request('d2')" />
                    </div>

                    <div>
                        <x-input-label for="status" value="Estatus" />
                        <select id="status" name="status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">-- Todos --</option>
                            @foreach (['borrador' => 'Borrador', 'aprobado' => 'Aprobado', 'rechazado' => 'Rechazado'] as $k => $v)
                                <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <x-primary-button>Filtrar</x-primary-button>
                        <a href="{{ route('expenses.index') }}"
                           class="rounded-md border px-3 py-2 text-sm hover:bg-gray-50">
                            Limpiar
                        </a>
                    </div>

                    <div class="ml-auto">
                        <a href="{{ route('expenses.create') }}"
                           class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                            Nuevo gasto
                        </a>
                    </div>
                </form>
            </div>

            {{-- Tabla --}}
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-sm">
                            <thead>
                                <tr class="bg-gray-100 text-left">
                                    <th class="border px-3 py-2">Fecha</th>
                                    <th class="border px-3 py-2">Proveedor</th>
                                    <th class="border px-3 py-2">Categoría</th>
                                    <th class="border px-3 py-2">Centro costo</th>
                                    <th class="border px-3 py-2 text-right">Monto</th>
                                    <th class="border px-3 py-2">Estatus</th>
                                    <th class="border px-3 py-2">Creado por</th>
                                    <th class="border px-3 py-2 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($expenses as $e)
                                    <tr class="hover:bg-gray-50">
                                        <td class="border px-3 py-2">
                                            {{ $e->fecha?->format('d/m/Y') }}
                                        </td>
                                        <td class="border px-3 py-2">
                                            {{ $e->proveedor ?? '—' }}
                                        </td>
                                        <td class="border px-3 py-2">
                                            {{ $e->category?->nombre ?? '—' }}
                                        </td>
                                        <td class="border px-3 py-2">
                                            {{ $e->costCenter?->nombre ?? '—' }}
                                        </td>
                                        <td class="border px-3 py-2 text-right">
                                            ${{ number_format($e->monto, 2) }}
                                        </td>
                                        <td class="border px-3 py-2">
                                            @php
                                                $map = [
                                                    'borrador'  => 'bg-gray-100 text-gray-700',
                                                    'aprobado'  => 'bg-green-100 text-green-700',
                                                    'rechazado' => 'bg-red-100 text-red-700',
                                                ];
                                            @endphp
                                            <span class="rounded px-2 py-0.5 text-xs {{ $map[$e->status] ?? 'bg-gray-100 text-gray-700' }}">
                                                {{ ucfirst($e->status) }}
                                            </span>
                                        </td>
                                        <td class="border px-3 py-2">
                                            {{ $e->creator?->name }}
                                        </td>

                                        <td class="border px-3 py-2 text-center">
                                            <div class="flex flex-wrap items-center justify-center gap-2">
                                                {{-- Ver detalle --}}
                                                <a href="{{ route('expenses.show', $e) }}"
                                                   class="rounded bg-blue-500 px-3 py-1 text-white hover:bg-blue-600">
                                                    Ver
                                                </a>

                                                {{-- Editar (policy controla ventas vs estados) --}}
                                                @can('update', $e)
                                                    <a href="{{ route('expenses.edit', $e) }}"
                                                       class="rounded bg-yellow-500 px-3 py-1 text-white hover:bg-yellow-600">
                                                        Editar
                                                    </a>
                                                @endcan

                                                {{-- Eliminar --}}
                                                @can('delete', $e)
                                                    <form action="{{ route('expenses.destroy', $e) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('¿Eliminar gasto?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="rounded bg-red-500 px-3 py-1 text-white hover:bg-red-600">
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-3 py-6 text-center text-gray-500" colspan="8">
                                            Sin resultados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $expenses->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
