<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Caja chica') }} – Detalle
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto space-y-4 sm:px-6 lg:px-8">

            @if (session('ok'))
                <div class="p-3 text-sm text-green-700 rounded-lg bg-green-50">
                    {{ session('ok') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-3 text-sm text-red-700 border border-red-200 rounded bg-red-50">
                    <ul class="pl-4 list-disc">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- RESUMEN DE LA SESIÓN --}}
            <div class="overflow-hidden bg-white rounded-lg shadow-sm">
                <div class="px-4 py-3 border-b">
                    <h3 class="text-base font-semibold text-gray-900">
                        Sesión de caja – {{ $session->fecha->format('d/m/Y') }}
                    </h3>
                    <p class="text-xs text-gray-500">
                        Vendedor: {{ $session->responsable?->name ?? '—' }}
                    </p>
                </div>

                <div class="grid gap-4 p-6 text-sm md:grid-cols-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-500">Saldo inicial</p>
                        <p class="mt-1 text-lg font-semibold">
                            ${{ number_format($session->saldo_inicial, 2) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500">Saldo actual (teórico)</p>
                        <p class="mt-1 text-lg font-semibold">
                            ${{ number_format($session->saldo_actual, 2) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500">Estatus</p>
                        @php
                            $map = [
                                'abierta' => 'bg-green-100 text-green-700',
                                'cerrada' => 'bg-gray-100 text-gray-700',
                            ];
                        @endphp
                        <span class="inline-flex px-2 py-0.5 mt-1 text-xs rounded {{ $map[$session->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst($session->status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500">Abierta por</p>
                        <p class="mt-1">
                            {{ $session->openedBy?->name ?? '—' }}
                        </p>
                    </div>
                </div>

                {{-- Info extra cuando la caja está cerrada --}}
                @if ($session->status === 'cerrada')
                    <div class="grid gap-4 px-6 pb-3 text-xs text-gray-600 md:grid-cols-3">
                        <div>
                            <p class="font-semibold">Efectivo contado</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                ${{ number_format($session->monto_arqueo, 2) }}
                            </p>
                        </div>

                        <div>
                            <p class="font-semibold">Diferencia</p>
                            <p class="mt-1 text-sm font-semibold {{ ($session->diferencia ?? 0) == 0 ? 'text-green-700' : 'text-red-700' }}">
                                ${{ number_format($session->diferencia ?? 0, 2) }}
                            </p>
                        </div>

                        <div>
                            <p class="font-semibold">Cerrada por / fecha</p>
                            <p class="mt-1 text-sm">
                                {{ $session->closedBy?->name ?? '—' }}<br>
                                {{ $session->cerrada_en?->format('d/m/Y H:i') ?? '—' }}
                            </p>
                        </div>
                    </div>

                    @if ($session->notas_cierre)
                        <div class="px-6 pb-6 text-xs text-gray-600">
                            <p class="font-semibold">Notas de cierre</p>
                            <p class="mt-1 text-sm text-gray-900 whitespace-pre-line">
                                {{ $session->notas_cierre }}
                            </p>
                        </div>
                    @endif
                @endif
            </div>

            {{-- FORMULARIO DE MOVIMIENTO (solo si está abierta) --}}
            @if ($session->status === 'abierta')
                <div class="overflow-hidden bg-white rounded-lg shadow-sm">
                    <div class="flex items-center justify-between px-4 py-3 border-b">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">
                                Nuevo movimiento
                            </h3>
                            <p class="text-xs text-gray-500">
                                Registra un ingreso (ej. anticipo) o un egreso operativo de la caja chica.
                            </p>
                        </div>

                        <a href="{{ route('petty-cash-sessions.close-form', $session) }}"
                           class="inline-flex items-center px-3 py-1 text-xs font-semibold text-white bg-red-500 rounded-md hover:bg-red-600">
                            Cerrar caja
                        </a>
                    </div>

                    <div class="p-6">
                        <form method="POST"
                              action="{{ route('petty-cash-movements.store', $session) }}"
                              enctype="multipart/form-data">
                            @csrf

                            {{-- Grid: Tipo / Monto / Categoría --}}
                            <div class="grid gap-4 md:grid-cols-3">
                                {{-- Tipo --}}
                                <div>
                                    <x-input-label for="tipo" value="Tipo" />
                                    <select id="tipo" name="tipo"
                                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                        <option value="ingreso" {{ old('tipo') === 'ingreso' ? 'selected' : '' }}>Ingreso</option>
                                        <option value="egreso"  {{ old('tipo') === 'egreso'  ? 'selected' : '' }}>Egreso</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
                                </div>

                                {{-- Monto --}}
                                <div>
                                    <x-input-label for="monto" value="Monto" />
                                    <x-text-input id="monto" name="monto" type="number" step="0.01"
                                                  class="block w-full mt-1"
                                                  :value="old('monto')" required />
                                    <x-input-error :messages="$errors->get('monto')" class="mt-2" />
                                </div>

                                {{-- Categoría (solo para egresos) --}}
                                <div>
                                    <x-input-label for="expense_category_id" value="Categoría (para egresos)" />
                                    <select id="expense_category_id" name="expense_category_id"
                                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                        <option value="">— Ninguna / ingreso —</option>
                                        @foreach ($expenseCategories as $c)
                                            <option value="{{ $c->id }}" @selected(old('expense_category_id') == $c->id)>
                                                {{ $c->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('expense_category_id')" class="mt-2" />
                                </div>
                            </div> {{-- /grid --}}

                            <div class="mt-4">
                                <x-input-label for="concepto" value="Concepto" />
                                <x-text-input id="concepto" name="concepto" type="text"
                                              class="block w-full mt-1"
                                              :value="old('concepto')" />
                                <x-input-error :messages="$errors->get('concepto')" class="mt-2" />
                            </div>

                            <div class="mt-4">
                                <x-input-label for="notas" value="Notas" />
                                <textarea id="notas" name="notas" rows="3"
                                          class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">{{ old('notas') }}</textarea>
                                <x-input-error :messages="$errors->get('notas')" class="mt-2" />
                            </div>

                            {{-- Comprobante (solo para egresos) --}}
                            <div class="mt-4" id="comprobante-wrapper">
                                <x-input-label for="comprobante" value="Comprobante (solo egresos)" />
                                <input id="comprobante" name="comprobante" type="file"
                                       class="block w-full mt-1 text-sm"
                                       accept="image/*,application/pdf">
                                <x-input-error :messages="$errors->get('comprobante')" class="mt-2" />
                                <p class="mt-1 text-xs text-gray-500">
                                    Formatos permitidos: JPG, PNG, PDF. Máx. 4&nbsp;MB.
                                </p>
                            </div>

                            <div class="flex justify-end gap-2 mt-6">
                                <a href="{{ route('petty-cash-sessions.index') }}"
                                   class="px-3 py-2 text-sm text-gray-700 border rounded-md hover:bg-gray-50">
                                    Volver a lista
                                </a>
                                <x-primary-button>
                                    Guardar movimiento
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- LISTA DE MOVIMIENTOS --}}
            <div class="overflow-hidden bg-white rounded-lg shadow-sm">
                <div class="px-4 py-3 border-b">
                    <h3 class="text-base font-semibold text-gray-900">
                        Movimientos
                    </h3>
                </div>

                <div class="p-4">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                        <tr class="text-left bg-gray-100">
                            <th class="px-3 py-2 border">Fecha / hora</th>
                            <th class="px-3 py-2 border">Tipo</th>
                            <th class="px-3 py-2 text-right border">Monto</th>
                            <th class="px-3 py-2 border">Categoría</th>
                            <th class="px-3 py-2 border">Concepto</th>
                            <th class="px-3 py-2 border">Comprobante</th>
                            <th class="px-3 py-2 border">Registró</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($session->movimientos as $m)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 border">
                                    {{ $m->created_at?->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-3 py-2 border">
                                    @if($m->tipo === 'ingreso')
                                        <span class="px-2 py-0.5 text-xs text-green-700 bg-green-100 rounded">Ingreso</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs text-red-700 bg-red-100 rounded">Egreso</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right border">
                                    ${{ number_format($m->monto, 2) }}
                                </td>
                                <td class="px-3 py-2 border">
                                    {{ $m->category?->nombre ?? '—' }}
                                </td>
                                <td class="px-3 py-2 border">
                                    {{ $m->concepto ?? '—' }}
                                </td>
                                <td class="px-3 py-2 border">
                                    @if($m->receipt_path)
                                        <span class="text-xs font-semibold text-blue-600">
                                            Comprobante adjunto
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 py-2 border">
                                    {{ $m->creator?->name ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-center text-gray-500">
                                    Aún no hay movimientos registrados.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tipoSelect         = document.getElementById('tipo');
    const catSelect          = document.getElementById('expense_category_id');
    const receiptWrapper     = document.getElementById('comprobante-wrapper');
    const receiptInput       = document.getElementById('comprobante');

    if (!tipoSelect || !catSelect) return;

    function toggleFields() {
        const isIngreso = tipoSelect.value === 'ingreso';

        // Categoría: solo para egresos
        catSelect.disabled = isIngreso;
        catSelect.classList.toggle('bg-gray-100', isIngreso);
        catSelect.classList.toggle('cursor-not-allowed', isIngreso);
        if (isIngreso) {
            catSelect.value = '';
        }

        // Comprobante: solo visible para egresos
        if (receiptWrapper && receiptInput) {
            receiptWrapper.classList.toggle('hidden', isIngreso);
            if (isIngreso) {
                receiptInput.value = '';
            }
        }
    }

    tipoSelect.addEventListener('change', toggleFields);
    toggleFields();
});
</script>
