<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Caja chica') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('ok'))
                <div class="p-3 mb-4 text-sm text-green-700 rounded-lg bg-green-50">
                    {{ session('ok') }}
                </div>
            @endif

            {{-- Filtros --}}
            <div class="p-4 mb-4 bg-white rounded-lg shadow-sm">
                <form method="GET" class="flex flex-wrap items-end gap-4">
                    <div>
                        <x-input-label for="status" value="Estatus" />
                        <select id="status" name="status"
                                class="block w-40 mt-1 text-sm border-gray-300 rounded-md shadow-sm">
                            <option value="">-- Todos --</option>
                            <option value="abierta" @selected(request('status') === 'abierta')>Abierta</option>
                            <option value="cerrada" @selected(request('status') === 'cerrada')>Cerrada</option>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="responsable_id" value="Vendedor" />
                        <select id="responsable_id" name="responsable_id"
                                class="block w-56 mt-1 text-sm border-gray-300 rounded-md shadow-sm">
                            <option value="">-- Todos --</option>
                            @foreach($vendedores as $v)
                                <option value="{{ $v->id }}" @selected(request('responsable_id') == $v->id)>
                                    {{ $v->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <x-primary-button>Filtrar</x-primary-button>
                        <a href="{{ route('petty-cash-sessions.index') }}"
                           class="px-3 py-2 text-sm border rounded-md hover:bg-gray-50">
                            Limpiar
                        </a>
                    </div>

                    <div class="ml-auto">
                        <a href="{{ route('petty-cash-sessions.create') }}"
                           class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                            Abrir caja
                        </a>
                    </div>
                </form>
            </div>

            {{-- Tabla --}}
            <div class="overflow-hidden bg-white rounded-lg shadow-sm">
                <div class="p-4">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                        <tr class="text-left bg-gray-100">
                            <th class="px-3 py-2 border">Fecha</th>
                            <th class="px-3 py-2 border">Vendedor</th>
                            <th class="px-3 py-2 text-right border">Saldo inicial</th>
                            <th class="px-3 py-2 text-center border">Estatus</th>
                            <th class="px-3 py-2 border">Abierta por</th>
                            <th class="px-3 py-2 text-center border">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($sessions as $s)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 border">
                                    {{ $s->fecha?->format('d/m/Y') }}
                                </td>
                                <td class="px-3 py-2 border">
                                    {{ $s->responsable?->name ?? '—' }}
                                </td>
                                <td class="px-3 py-2 text-right border">
                                    ${{ number_format($s->saldo_inicial, 2) }}
                                </td>
                                <td class="px-3 py-2 text-center border">
                                    @php
                                        $map = [
                                            'abierta' => 'bg-green-100 text-green-700',
                                            'cerrada' => 'bg-gray-100 text-gray-700',
                                        ];
                                    @endphp
                                    <span class="px-2 py-0.5 text-xs rounded {{ $map[$s->status] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ ucfirst($s->status) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 border">
                                    {{ $s->openedBy?->name ?? '—' }}
                                </td>
                                <td class="px-3 py-2 text-center border">
                                    <a href="{{ route('petty-cash-sessions.show', $s) }}"
                                    class="px-3 py-1 text-xs font-semibold text-white bg-indigo-600 rounded hover:bg-indigo-700">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-6 text-center text-gray-500">
                                    No hay sesiones de caja registradas.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $sessions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
