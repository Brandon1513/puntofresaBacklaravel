<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Nuevo ajuste – {{ $item->nombre }} ({{ $item->sku }})
            </h2>
            <a href="{{ route('items.ajustes.index', $item) }}"
               class="px-3 py-1 text-sm text-gray-700 bg-white border rounded-md hover:bg-gray-50">
                Volver a bitácora
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white rounded shadow-sm">
                <div class="p-6">

                    @if ($errors->any())
                        <div class="p-3 mb-4 text-sm text-red-700 rounded bg-red-50">
                            <ul class="pl-5 space-y-1 list-disc">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST"
                          action="{{ route('items.ajustes.store', $item) }}"
                          enctype="multipart/form-data">
                        @csrf

                        {{-- Tipo --}}
                        <div class="mb-4">
                            <x-input-label for="tipo" value="Tipo de ajuste" />
                            <select id="tipo" name="tipo"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm" required>
                                <option value="">— Selecciona —</option>
                                <option value="entrada"   @selected(old('tipo') === 'entrada')>Entrada</option>
                                <option value="compra"    @selected(old('tipo') === 'compra')>Compra</option>
                                <option value="merma"     @selected(old('tipo') === 'merma')>Merma</option>
                                <option value="dano"      @selected(old('tipo') === 'dano')>Daño</option>
                                <option value="correccion" @selected(old('tipo') === 'correccion')>Corrección</option>
                            </select>
                            <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
                        </div>

                        {{-- Cantidad --}}
                        <div class="mb-4">
                            <x-input-label for="cantidad" value="Cantidad" />
                            <x-text-input id="cantidad" name="cantidad" type="number" min="1"
                                          class="block w-full mt-1"
                                          :value="old('cantidad', 1)" required />
                            <x-input-error :messages="$errors->get('cantidad')" class="mt-2" />
                        </div>

                        {{-- Motivo --}}
                        <div class="mb-4">
                            <x-input-label for="motivo" value="Motivo / comentario" />
                            <textarea id="motivo" name="motivo" rows="3"
                                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">{{ old('motivo') }}</textarea>
                            <x-input-error :messages="$errors->get('motivo')" class="mt-2" />
                        </div>

                        {{-- Evidencia --}}
                        <div class="mb-4">
                            <x-input-label for="evidencia" value="Evidencia (foto o PDF)" />
                            <input id="evidencia" name="evidencia" type="file"
                                   class="block w-full mt-1 text-sm text-gray-700 border-gray-300 rounded-md shadow-sm">
                            <p class="mt-1 text-xs text-gray-500">
                                Opcional. Formatos permitidos: JPG, PNG, PDF. Máx 5 MB.
                            </p>
                            <x-input-error :messages="$errors->get('evidencia')" class="mt-2" />
                        </div>

                        <div class="flex justify-end gap-2 mt-6">
                            <a href="{{ route('items.ajustes.index', $item) }}"
                               class="px-3 py-2 text-sm text-gray-700 border rounded-md hover:bg-gray-50">
                                Cancelar
                            </a>
                            <x-primary-button>
                                Guardar ajuste
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
