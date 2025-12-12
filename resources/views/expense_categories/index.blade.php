<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Categorías de gastos') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('ok'))
                <div class="p-3 mb-4 text-sm text-green-700 rounded-lg bg-green-50">
                    {{ session('ok') }}
                </div>
            @endif

            <div class="flex justify-end mb-4">
                <a href="{{ route('expense-categories.create') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                    Nueva categoría
                </a>
            </div>

            <div class="overflow-hidden bg-white rounded-lg shadow-sm">
                <div class="p-4">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                        <tr class="text-left bg-gray-100">
                            <th class="px-3 py-2 border">Nombre</th>
                            <th class="px-3 py-2 border">Activo</th>
                            <th class="px-3 py-2 text-center border">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($cats as $cat)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 border">
                                    {{ $cat->nombre }}
                                </td>
                                <td class="px-3 py-2 border">
                                    @if($cat->activo)
                                        <span class="rounded bg-green-100 px-2 py-0.5 text-xs text-green-700">Sí</span>
                                    @else
                                        <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">No</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-center border">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('expense-categories.edit', $cat) }}"
                                           class="px-3 py-1 text-xs text-white bg-yellow-500 rounded hover:bg-yellow-600">
                                            Editar
                                        </a>

                                        <form action="{{ route('expense-categories.destroy', $cat) }}"
                                              method="POST"
                                              onsubmit="return confirm('¿Eliminar categoría?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-3 py-1 text-xs text-white bg-red-500 rounded hover:bg-red-600">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-6 text-center text-gray-500">
                                    No hay categorías registradas.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $cats->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
