<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Editar paquete – {{ $bundle->nombre }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('bundles.update', $bundle) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('bundles._form', ['bundle' => $bundle])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
