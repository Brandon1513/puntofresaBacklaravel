<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Detalle del gasto') }} #{{ $expense->id }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                @if (session('ok'))
                    <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-700">
                        {{ session('ok') }}
                    </div>
                @endif

                {{-- Encabezado --}}
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Fecha</p>
                        <p class="text-lg font-semibold">
                            {{ $expense->fecha?->format('d/m/Y') }}
                        </p>
                    </div>

                    @php
                        $map = [
                            'borrador'  => 'bg-gray-100 text-gray-700',
                            'aprobado'  => 'bg-green-100 text-green-700',
                            'rechazado' => 'bg-red-100 text-red-700',
                        ];
                    @endphp
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $map[$expense->status] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ ucfirst($expense->status) }}
                    </span>
                </div>

                {{-- Detalle en grid --}}
                <div class="mb-6 grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-gray-500">Proveedor</p>
                        <p class="font-medium">{{ $expense->proveedor ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Categoría</p>
                        <p class="font-medium">{{ $expense->category?->nombre ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Centro de costo</p>
                        <p class="font-medium">{{ $expense->costCenter?->nombre ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Monto</p>
                        <p class="text-xl font-bold">
                            ${{ number_format($expense->monto, 2) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Método de pago</p>
                        <p class="font-medium">{{ $expense->metodo_pago ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Referencia</p>
                        <p class="font-medium">{{ $expense->referencia ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Creado por</p>
                        <p class="font-medium">{{ $expense->creator?->name ?? '—' }}</p>
                    </div>

                    @if ($expense->approved_by)
                        <div>
                            <p class="text-sm text-gray-500">Aprobado por</p>
                            <p class="font-medium">
                                {{ optional(\App\Models\User::find($expense->approved_by))->name ?? '—' }}
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Notas --}}
                @if ($expense->notas)
                    <div class="mb-6">
                        <p class="text-sm text-gray-500">Notas</p>
                        <p class="mt-1 whitespace-pre-line text-gray-800">
                            {{ $expense->notas }}
                        </p>
                    </div>
                @endif

                {{-- 🔗 Adjuntos: ver y descargar --}}
                @if ($expense->attachments?->count())
                    <div class="mb-6">
                        <p class="mb-2 text-sm text-gray-500">Comprobantes adjuntos</p>

                        <div class="space-y-2">
                            @foreach ($expense->attachments as $a)
                                @php
                                    $url = Storage::url($a->path);
                                @endphp
                                <div class="flex items-center justify-between rounded border px-3 py-2 text-sm">
                                    <div class="flex flex-col">
                                        <span class="font-medium">
                                            {{ $a->original_name ?? basename($a->path) }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ $a->mime ?? 'Archivo' }}
                                            @if($a->size)
                                                • {{ number_format($a->size / 1024, 0) }} KB
                                            @endif
                                        </span>
                                    </div>

                                    <div class="flex gap-2">
                                        {{-- Ver en nueva pestaña (PDF / imagen) --}}
                                        <a href="{{ $url }}" target="_blank"
                                           class="rounded bg-indigo-500 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-600">
                                            Ver
                                        </a>

                                        {{-- Forzar descarga --}}
                                        <a href="{{ $url }}" download
                                           class="rounded bg-gray-600 px-3 py-1 text-xs font-semibold text-white hover:bg-gray-700">
                                            Descargar
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Botones Aprobar / Rechazar --}}
                @can('approve', $expense)
                    <div class="mb-6 flex flex-wrap gap-3">
                        @if (in_array($expense->status, ['borrador', 'rechazado']))
                            <form method="POST" action="{{ route('expenses.approve', $expense) }}">
                                @csrf
                                <button type="submit"
                                        class="rounded bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                                    Aprobar
                                </button>
                            </form>
                        @endif

                        @if ($expense->status === 'borrador')
                            <form method="POST" action="{{ route('expenses.reject', $expense) }}">
                                @csrf
                                <button type="submit"
                                        class="rounded bg-gray-600 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                                    Rechazar
                                </button>
                            </form>
                        @endif
                    </div>
                @endcan

                {{-- Volver --}}
                <div>
                    <a href="{{ route('expenses.index') }}"
                       class="text-sm text-gray-600 hover:text-gray-900">
                        ← Volver al listado
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
