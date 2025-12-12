<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Unidades de ítems') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto space-y-4 sm:px-6 lg:px-8">

            @if (session('ok'))
                <div class="p-3 text-sm text-green-700 rounded bg-green-50">
                    {{ session('ok') }}
                </div>
            @endif

            <div class="overflow-hidden bg-white rounded shadow-sm">
                <div class="flex items-center justify-between px-4 py-3 border-b">
                    <h3 class="text-base font-semibold text-gray-900">
                        Lista de unidades
                    </h3>
                    <a href="{{ route('unidades.create') }}"
                       class="px-3 py-1 text-sm font-semibold text-white bg-indigo-600 rounded hover:bg-indigo-700">
                        Nueva unidad
                    </a>
                </div>

                <div class="p-4">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                        <tr class="text-left bg-gray-100">
                            <th class="px-3 py-2 border">Nombre</th>
                            <th class="px-3 py-2 border">Abreviatura</th>
                            <th class="px-3 py-2 border">Estatus</th>
                            <th class="w-40 px-3 py-2 border">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($unidades as $u)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 border">{{ $u->nombre }}</td>
                                <td class="px-3 py-2 border">{{ $u->abreviatura ?? '—' }}</td>
                                <td class="px-3 py-2 border">
                                    @if($u->activo)
                                        <span class="px-2 py-0.5 text-xs text-green-700 bg-green-100 rounded">Activa</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs text-gray-700 bg-gray-100 rounded">Inactiva</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 border">
                                    <div class="flex gap-2">
                                        <a href="{{ route('unidades.edit', $u) }}"
                                           class="text-xs text-indigo-600 hover:underline">
                                            Editar
                                        </a>

                                        <form method="POST"
                                              action="{{ route('unidades.toggle', $u) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="text-xs text-gray-700 hover:underline">
                                                {{ $u->activo ? 'Inactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-sm text-center text-gray-500">
                                    Aún no hay unidades registradas.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $unidades->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
