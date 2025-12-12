<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Editar cliente
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('clientes.update', $cliente) }}">
                        @csrf
                        @method('PUT')
                        @include('clientes._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
