<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Nuevo usuario') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                <div class="border-b p-4">
                    <h3 class="text-lg font-medium text-gray-900">Registro Nuevo</h3>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('users.store') }}">
                        @csrf
                        @include('users._form', ['user' => new \App\Models\User(), 'roles' => $roles, 'selected' => []])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
