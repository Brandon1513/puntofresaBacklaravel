<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Centros de costo') }}
        </h2>
    </x-slot>

    <div class="py-6">
        {{-- Contenedor más estrecho --}}
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if (session('ok'))
                <div class="p-3 mb-4 text-sm text-green-700 rounded-lg bg-green-50">
                    {{ session('ok') }}
                </div>
            @endif

            <div class="flex items-center justify-between mb-4">
                <p class="text-sm text-gray-600">
                    Total: <span class="font-semibold">{{ $ccs->total() }}</span> centros de costo
                </p>

                <a href="{{ route('cost-centers.create') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                    Nuevo centro de costo
                </a>
            </div>

            <div class="overflow-hidden bg-white rounded-lg shadow-sm">
                <div class="p-4">
                    <table class="w-full text-xs border-collapse md:text-sm">
                        <thead>
                        <tr class="text-left bg-gray-100">
                            <th class="w-2/3 px-3 py-2 border">Nombre</th>
                            <th class="w-1/6 px-3 py-2 border">Activo</th>
                            <th class="w-1/6 px-3 py-2 text-center border">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($ccs as $cc)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-1.5 border">
                                    {{ $cc->nombre }}
                                </td>
                                <td class="px-3 py-1.5 border">
                                    @if($cc->activo)
                                        <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium text-green-700 bg-green-100 rounded">
                                            Sí
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium text-gray-600 bg-gray-100 rounded">
                                            No
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-1.5 text-center border">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('cost-centers.edit', $cc) }}"
                                           class="px-2.5 py-1 text-[11px] text-white bg-yellow-500 rounded hover:bg-yellow-600">
                                            Editar
                                        </a>

                                        <form action="{{ route('cost-centers.destroy', $cc) }}"
                                              method="POST"
                                              onsubmit="return confirm('¿Eliminar centro de costo?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-2.5 py-1 text-[11px] text-white bg-red-500 rounded hover:bg-red-600">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-6 text-center text-gray-500">
                                    No hay centros de costo registrados.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $ccs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
