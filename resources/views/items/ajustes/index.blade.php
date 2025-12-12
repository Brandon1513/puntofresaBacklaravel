<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Bitácora de inventario – {{ $item->nombre }} ({{ $item->sku }})
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('items.show', $item) }}"
                   class="px-3 py-1 text-sm text-gray-700 bg-white border rounded-md hover:bg-gray-50">
                    Volver al ítem
                </a>
                <a href="{{ route('items.ajustes.create', $item) }}"
                   class="px-3 py-1 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                    Nuevo ajuste
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            @if (session('ok'))
                <div class="p-3 mb-4 text-sm text-green-700 rounded bg-green-50">
                    {{ session('ok') }}
                </div>
            @endif

            <div class="overflow-hidden bg-white rounded shadow-sm">
                <div class="px-4 py-3 border-b">
                    <p class="text-sm text-gray-600">
                        Stock actual: <span class="font-semibold">{{ $item->stock_fisico }}</span>
                    </p>
                </div>

                <div class="p-4 overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                        <tr class="text-left bg-gray-100">
                            <th class="px-3 py-2 border">Fecha</th>
                            <th class="px-3 py-2 border">Usuario</th>
                            <th class="px-3 py-2 border">Tipo</th>
                            <th class="px-3 py-2 text-right border">Cantidad</th>
                            <th class="px-3 py-2 border">Motivo</th>
                            <th class="px-3 py-2 border">Evidencia</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($ajustes as $aj)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-xs text-gray-500 border">
                                    {{ $aj->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-3 py-2 border">
                                    {{ $aj->user?->name ?? '—' }}
                                </td>
                                <td class="px-3 py-2 capitalize border">
                                    {{ $aj->tipo }}
                                </td>
                                <td class="px-3 py-2 text-right border">
                                    {{ $aj->cantidad }}
                                </td>
                                <td class="px-3 py-2 border">
                                    {{ $aj->motivo ?: '—' }}
                                </td>
                                <td class="px-3 py-2 border">
                                    @if($aj->evidencia_path)
                                        <a href="{{ asset('storage/'.$aj->evidencia_path) }}"
                                           target="_blank"
                                           class="text-xs text-indigo-600 hover:underline">
                                            Ver evidencia
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-6 text-sm text-center text-gray-500">
                                    Aún no hay ajustes registrados para este ítem.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $ajustes->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
