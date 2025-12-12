<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Caja chica') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white rounded-lg shadow-sm">
                <div class="px-4 py-3 border-b">
                    <h3 class="text-base font-semibold text-gray-900">
                        Abrir caja chica
                    </h3>
                    <p class="text-xs text-gray-500">
                        Elige al vendedor responsable y define el saldo inicial para la caja.
                    </p>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('petty-cash-sessions.store') }}">
                        @csrf

                        {{-- Fecha --}}
                        <div class="mb-4">
                            <x-input-label for="fecha" value="Fecha" />
                            <x-text-input id="fecha" name="fecha" type="date"
                                class="block w-full mt-1"
                                :value="old('fecha', now()->format('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('fecha')" class="mt-2" />
                        </div>

                        {{-- Vendedor responsable --}}
                        <div class="mb-4">
                            <x-input-label for="responsable_id" value="Vendedor responsable" />
                            <select id="responsable_id" name="responsable_id"
                                    class="block w-full mt-1 text-sm border-gray-300 rounded-md shadow-sm" required>
                                <option value="">— Selecciona —</option>
                                @foreach($vendedores as $v)
                                    <option value="{{ $v->id }}" @selected(old('responsable_id') == $v->id)>
                                        {{ $v->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('responsable_id')" class="mt-2" />
                        </div>

                        {{-- Saldo inicial --}}
                        <div class="mb-4">
                            <x-input-label for="saldo_inicial" value="Saldo inicial" />
                            <x-text-input id="saldo_inicial" name="saldo_inicial" type="number" step="0.01" min="0"
                                class="block w-full mt-1"
                                :value="old('saldo_inicial', '0.00')" required />
                            <x-input-error :messages="$errors->get('saldo_inicial')" class="mt-2" />
                        </div>

                        <div class="flex justify-end gap-2 mt-6">
                            <a href="{{ route('petty-cash-sessions.index') }}"
                               class="px-3 py-2 text-sm text-gray-700 border rounded-md hover:bg-gray-50">
                                Cancelar
                            </a>

                            <x-primary-button>
                                Abrir caja
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
