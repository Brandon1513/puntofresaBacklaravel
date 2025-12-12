<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Editar ítem de catálogo') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white rounded shadow-sm">
                <div class="px-4 py-3 border-b">
                    <h3 class="text-base font-semibold text-gray-900">
                        Actualizar ítem
                    </h3>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('items.update', $item) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('items._form', ['categorias' => $categorias, 'unidades' => $unidades, 'item' => $item])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
