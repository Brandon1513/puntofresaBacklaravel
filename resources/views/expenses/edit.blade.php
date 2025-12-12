<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Editar gasto') }} #{{ $expense->id }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="border-b p-4">
                    <h3 class="text-lg font-medium text-gray-900">Edición</h3>
                </div>

                <div class="p-6">
                    {{-- Mensajes --}}
                    @if (session('ok'))
                        <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-700">
                            {{ session('ok') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST"
                          action="{{ route('expenses.update', $expense) }}"
                          enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        @include('expenses._form', [
                            'expense' => $expense,
                            'cats'    => $cats,
                            'ccs'     => $ccs,
                        ])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
