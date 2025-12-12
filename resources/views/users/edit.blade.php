<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Editar usuario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if ($errors->any())
                        <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">
                            Revisa los campos marcados.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        @include('users._form', [
                            'user'     => $user,        // ← importante
                            'roles'    => $roles,       // colección de Role con id,name
                            'selected' => $selected     // array de IDs de roles del usuario
                        ])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
