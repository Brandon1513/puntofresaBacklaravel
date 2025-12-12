<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Caja chica') }} – Cierre
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto space-y-4 sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="p-3 text-sm text-red-700 border border-red-200 rounded bg-red-50">
                    <ul class="pl-4 list-disc">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Resumen rápido --}}
            <div class="overflow-hidden bg-white rounded-lg shadow-sm">
                <div class="px-4 py-3 border-b">
                    <h3 class="text-base font-semibold text-gray-900">
                        Sesión de caja – {{ $session->fecha->format('d/m/Y') }}
                    </h3>
                    <p class="text-xs text-gray-500">
                        Vendedor: {{ $session->responsable?->name ?? '—' }}
                    </p>
                </div>
                <div class="grid gap-4 p-6 text-sm md:grid-cols-3">
                    <div>
                        <p class="text-xs font-semibold text-gray-500">Saldo inicial</p>
                        <p class="mt-1 text-lg font-semibold">
                            ${{ number_format($session->saldo_inicial, 2) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500">Saldo esperado (teórico)</p>
                        <p class="mt-1 text-lg font-semibold">
                            ${{ number_format($session->saldo_actual, 2) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500">Abierta por</p>
                        <p class="mt-1">
                            {{ $session->openedBy?->name ?? '—' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Formulario de cierre --}}
            <div class="overflow-hidden bg-white rounded-lg shadow-sm">
                <div class="px-4 py-3 border-b">
                    <h3 class="text-base font-semibold text-gray-900">
                        Arqueo y cierre de caja
                    </h3>
                    <p class="text-xs text-gray-500">
                        Captura el efectivo contado físicamente para conciliar la caja.
                    </p>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('petty-cash-sessions.close', $session) }}">
                        @csrf

                        <div class="mb-4">
                            <x-input-label value="Saldo esperado (teórico)" />
                            <input type="text"
                                   class="block w-full mt-1 text-sm bg-gray-100 border-gray-200 rounded-md"
                                   value="${{ number_format($session->saldo_actual, 2) }}"
                                   disabled>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <x-input-label for="monto_arqueo" value="Efectivo contado (arqueo)" />
                                <x-text-input id="monto_arqueo" name="monto_arqueo" type="number" step="0.01"
                                              class="block w-full mt-1"
                                              :value="old('monto_arqueo')" required />
                                <x-input-error :messages="$errors->get('monto_arqueo')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label value="Diferencia (contado - teórico)" />
                                <input id="diferencia_mostrada" type="text"
                                       class="block w-full mt-1 text-sm bg-gray-100 border-gray-200 rounded-md"
                                       value="$0.00" disabled>
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="notas_cierre" value="Notas de cierre (opcional)" />
                            <textarea id="notas_cierre" name="notas_cierre" rows="3"
                                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">{{ old('notas_cierre') }}</textarea>
                            <x-input-error :messages="$errors->get('notas_cierre')" class="mt-2" />
                        </div>

                        <div class="flex justify-end gap-2 mt-6">
                            <a href="{{ route('petty-cash-sessions.show', $session) }}"
                               class="px-3 py-2 text-sm text-gray-700 border rounded-md hover:bg-gray-50">
                                Cancelar
                            </a>
                            <x-primary-button
                                onclick="return confirm('¿Cerrar definitivamente esta caja chica?');">
                                Cerrar caja
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const esperado   = {{ $session->saldo_actual ?? 0 }};
    const inputArq   = document.getElementById('monto_arqueo');
    const diffOutput = document.getElementById('diferencia_mostrada');

    if (!inputArq || !diffOutput) return;

    function formatMoney(n) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN'
        }).format(Number(n || 0));
    }

    function recalc() {
        const val = parseFloat(inputArq.value || 0);
        const diff = val - esperado;
        diffOutput.value = formatMoney(diff);
    }

    inputArq.addEventListener('input', recalc);
    recalc();
});
</script>
