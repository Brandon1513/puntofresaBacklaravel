<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Nuevo gasto') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="border-b p-4">
                    <h3 class="text-lg font-medium text-gray-900">Captura</h3>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data">
                        @csrf
                        @include('expenses._form', ['cats'=>$cats,'ccs'=>$ccs])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
