<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Caja chica') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            @if (session('ok'))
                <div class="rounded-lg bg-green-50 p-3 text-sm text-green-700">{{ session('ok') }}</div>
            @endif
            @error('saldo_real_cierre')
                <div class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $message }}</div>
            @enderror

            {{-- Estado de la sesión --}}
            <div class="grid gap-6 md:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="mb-2 text-sm font-medium text-gray-600">Caja</h3>
                    <div class="text-xl font-semibold">{{ $box->nombre }}</div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="mb-2 text-sm font-medium text-gray-600">Sesión</h3>
                    @if($session)
                        <div class="text-sm">
                            <div><b>Apertura:</b> {{ $session->opened_at->format('d/m/Y H:i') }}</div>
                            <div><b>Estado:</b> <span class="rounded bg-green-100 px-2 py-0.5 text-xs text-green-700">Abierta</span></div>
                            <div class="mt-1"><b>Saldo actual:</b> ${{ number_format($saldo,2) }}</div>
                        </div>
                    @else
                        <div class="text-sm text-gray-500">No hay sesión abierta.</div>
                    @endif
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="mb-2 text-sm font-medium text-gray-600">Acciones</h3>
                    @if(!$session)
                        <form method="POST" action="{{ route('cash.open') }}" class="flex items-end gap-3">
                            @csrf
                            <input type="hidden" name="cash_box_id" value="{{ $box->id }}">
                            <div>
                                <x-input-label for="saldo_inicial" value="Saldo inicial" />
                                <x-text-input id="saldo_inicial" name="saldo_inicial" type="number" step="0.01" class="mt-1 block w-40" required />
                            </div>
                            <x-primary-button>Abrir caja</x-primary-button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('cash.close',$session) }}" class="flex items-end gap-3">
                            @csrf
                            <div>
                                <x-input-label for="saldo_real_cierre" value="Saldo real" />
                                <x-text-input id="saldo_real_cierre" name="saldo_real_cierre" type="number" step="0.01" class="mt-1 block w-40" required />
                            </div>
                            <x-primary-button class="bg-red-600 hover:bg-red-700">Cerrar caja</x-primary-button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Registro de movimientos --}}
            @if($session)
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-semibold">Nuevo movimiento</h3>
                        <form method="POST" action="{{ route('cash.movement') }}" class="grid grid-cols-2 gap-4">
                            @csrf
                            <input type="hidden" name="cash_session_id" value="{{ $session->id }}">

                            <div>
                                <x-input-label for="tipo" value="Tipo" />
                                <select id="tipo" name="tipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="ingreso">Ingreso</option>
                                    <option value="egreso">Egreso</option>
                                </select>
                            </div>

                            <div>
                                <x-input-label for="fecha" value="Fecha" />
                                <x-text-input id="fecha" name="fecha" type="datetime-local" class="mt-1 block w-full"
                                              :value="now()->format('Y-m-d\TH:i')" required />
                            </div>

                            <div>
                                <x-input-label for="monto" value="Monto" />
                                <x-text-input id="monto" name="monto" type="number" step="0.01" class="mt-1 block w-full" required />
                            </div>

                            <div>
                                <x-input-label for="metodo" value="Método" />
                                <x-text-input id="metodo" name="metodo" type="text" class="mt-1 block w-full" placeholder="Efectivo, transferencia..." />
                            </div>

                            <div class="col-span-2">
                                <x-input-label for="referencia" value="Referencia" />
                                <x-text-input id="referencia" name="referencia" type="text" class="mt-1 block w-full" />
                            </div>

                            <div class="col-span-2">
                                <x-input-label for="notas" value="Notas" />
                                <textarea id="notas" name="notas" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                            </div>

                            <div class="col-span-2 flex justify-end">
                                <x-primary-button>Agregar</x-primary-button>
                            </div>
                        </form>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-semibold">Movimientos</h3>
                        <div class="max-h-[420px] overflow-auto">
                            <table class="w-full border-collapse text-sm">
                                <thead>
                                    <tr class="bg-gray-100 text-left">
                                        <th class="border px-3 py-2">Fecha</th>
                                        <th class="border px-3 py-2">Tipo</th>
                                        <th class="border px-3 py-2 text-right">Monto</th>
                                        <th class="border px-3 py-2">Método</th>
                                        <th class="border px-3 py-2">Ref</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($movs as $m)
                                        <tr>
                                            <td class="border px-3 py-2">{{ \Carbon\Carbon::parse($m->fecha)->format('d/m/Y H:i') }}</td>
                                            <td class="border px-3 py-2">
                                                <span class="rounded px-2 py-0.5 text-xs {{ $m->tipo==='ingreso' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                    {{ ucfirst($m->tipo) }}
                                                </span>
                                            </td>
                                            <td class="border px-3 py-2 text-right">${{ number_format($m->monto,2) }}</td>
                                            <td class="border px-3 py-2">{{ $m->metodo ?? '—' }}</td>
                                            <td class="border px-3 py-2">{{ $m->referencia ?? '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td class="px-3 py-6 text-center text-gray-500" colspan="5">Sin movimientos.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
