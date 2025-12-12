<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Detalle del ítem') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('items.edit', $item) }}"
                   class="px-3 py-1 text-sm text-white bg-blue-600 rounded-md hover:bg-blue-700">
                    Editar
                </a>
                <a href="{{ route('items.index') }}"
                   class="px-3 py-1 text-sm text-gray-700 bg-white border rounded-md hover:bg-gray-50">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    {{-- Encabezado: nombre + estado --}}
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">
                                {{ $item->nombre }}
                            </h1>
                            <p class="text-sm text-gray-500">
                                SKU: {{ $item->sku }}
                            </p>
                        </div>

                        @if($item->activo)
                            <span class="px-3 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">
                                Activo
                            </span>
                        @else
                            <span class="px-3 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">
                                Inactivo
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        {{-- Columna izquierda: fotos --}}
                        <div>
                            @php
                                $mainPhoto = $item->photos->firstWhere('es_principal', true)
                                            ?? $item->photos->first();
                            @endphp

                            @if($mainPhoto)
                                {{-- Foto principal (click para ver en grande) --}}
                                <div class="mb-4 overflow-hidden border rounded-lg">
                                    <img
                                        id="main-photo"
                                        src="{{ asset('storage/'.$mainPhoto->path) }}"
                                        alt="Foto principal"
                                        class="object-cover w-full h-64 transition-all duration-200 cursor-pointer"
                                    >
                                </div>

                                {{-- Miniaturas clicables --}}
                                @if($item->photos->count() > 1)
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($item->photos as $idx => $photo)
                                            <img
                                                src="{{ asset('storage/'.$photo->path) }}"
                                                data-photo-thumb="1"
                                                data-full="{{ asset('storage/'.$photo->path) }}"
                                                data-index="{{ $idx }}"
                                                class="object-cover w-16 h-16 border rounded-md cursor-pointer
                                                    {{ $photo->id === $mainPhoto->id ? 'ring-2 ring-blue-500' : '' }}"
                                                alt="Foto ítem"
                                            >
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                <div class="flex items-center justify-center w-full h-64 text-sm text-gray-400 border border-dashed rounded-lg">
                                    Sin fotos cargadas
                                </div>
                            @endif
                        </div>

                        {{-- Columna derecha: datos --}}
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-600 uppercase">
                                    Información general
                                </h3>
                                <dl class="mt-2 space-y-1 text-sm text-gray-700">
                                    <div class="flex">
                                        <dt class="w-32 font-medium">Categoría</dt>
                                        <dd>{{ $item->categoria->nombre ?? '-' }}</dd>
                                    </div>
                                    <div class="flex">
                                        <dt class="w-32 font-medium">Unidad</dt>
                                        <dd>
                                            {{ $item->unidad->nombre ?? '-' }}
                                            @if($item->unidad?->abreviatura)
                                                ({{ $item->unidad->abreviatura }})
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="flex">
                                        <dt class="w-32 font-medium">Ubicación</dt>
                                        <dd>{{ $item->ubicacion ?: '-' }}</dd>
                                    </div>
                                </dl>

                                {{-- QR DEL ÍTEM --}}
                                @php
                                    // Texto que va codificado en el QR
                                    $qrText = $item->qr_code ?: ('ITM-' . str_pad($item->id, 6, '0', STR_PAD_LEFT));
                                @endphp

                                <div class="mt-4">
                                    <h3 class="text-sm font-semibold text-gray-600 uppercase">
                                        Código QR
                                    </h3>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Escanéalo para identificar este ítem.
                                    </p>

                                    <div class="inline-block p-3 mt-2 bg-white border rounded-lg shadow-sm">
                                        <img
                                            src="{{ 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($qrText) }}"
                                            alt="QR {{ $qrText }}"
                                            class="w-40 h-40"
                                        >
                                    </div>

                                    <p class="mt-2 text-xs text-gray-500">
                                        Código: <span class="font-mono text-gray-700">{{ $qrText }}</span>
                                    </p>

                                    {{-- Botón descargar QR --}}
                                    <a href="{{ route('items.qr.download', $item) }}"
                                       class="inline-flex items-center px-3 py-1 mt-3 text-xs font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                                        Descargar QR
                                    </a>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm font-semibold text-gray-600 uppercase">
                                    Precios y costos
                                </h3>
                                <dl class="mt-2 space-y-1 text-sm text-gray-700">
                                    <div class="flex">
                                        <dt class="w-40 font-medium">Renta por día</dt>
                                        <dd>
                                            {{ $item->precio_renta_dia ? '$'.number_format($item->precio_renta_dia,2) : '-' }}
                                        </dd>
                                    </div>
                                    <div class="flex">
                                        <dt class="w-40 font-medium">Renta fin de semana</dt>
                                        <dd>
                                            {{ $item->precio_renta_fin ? '$'.number_format($item->precio_renta_fin,2) : '-' }}
                                        </dd>
                                    </div>
                                    <div class="flex">
                                        <dt class="w-40 font-medium">Costo promedio</dt>
                                        <dd>
                                            {{ $item->costo_promedio ? '$'.number_format($item->costo_promedio,2) : '-' }}
                                        </dd>
                                    </div>
                                    <div class="flex">
                                        <dt class="w-40 font-medium">Costo reposición</dt>
                                        <dd>
                                            {{ $item->costo_reposicion ? '$'.number_format($item->costo_reposicion,2) : '-' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-sm font-semibold text-gray-600 uppercase">
                                    Stock
                                </h3>
                                <dl class="mt-2 space-y-1 text-sm text-gray-700">
                                    <div class="flex">
                                        <dt class="w-40 font-medium">Stock físico</dt>
                                        <dd>{{ $item->stock_fisico }}</dd>
                                    </div>
                                    <div class="flex">
                                        <dt class="w-40 font-medium">Stock mínimo</dt>
                                        <dd>{{ $item->stock_minimo }}</dd>
                                    </div>
                                </dl>
                            </div>

                            @if($item->tags)
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-600 uppercase">
                                        Tags
                                    </h3>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        @foreach(explode(',', $item->tags) as $tag)
                                            <span class="px-2 py-1 text-xs text-gray-700 bg-gray-100 rounded-full">
                                                {{ trim($tag) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Descripción --}}
                    @if($item->descripcion)
                        <div class="pt-6 mt-6 border-t border-gray-200">
                            <h3 class="mb-2 text-sm font-semibold text-gray-600 uppercase">
                                Descripción
                            </h3>
                            <p class="text-sm leading-relaxed text-gray-800">
                                {{ $item->descripcion }}
                            </p>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    {{-- Modal visor de fotos --}}
    <div
        id="photo-viewer"
        class="fixed inset-0 z-50 items-center justify-center hidden bg-black/70"
    >
        <div class="relative max-w-4xl max-h-[90vh] mx-auto px-4">
            {{-- Botón cerrar --}}
            <button
                type="button"
                id="photo-viewer-close"
                class="absolute z-10 p-2 text-white rounded-full bg-black/60 -right-3 -top-3 hover:bg-black"
            >
                ✕
            </button>

            {{-- Botón anterior --}}
            <button
                type="button"
                id="photo-viewer-prev"
                class="absolute left-0 z-10 flex items-center justify-center w-10 h-10 mt-[45%] -translate-y-1/2 text-white bg-black/60 rounded-full hover:bg-black"
            >
                ‹
            </button>

            {{-- Imagen grande --}}
            <div class="overflow-hidden bg-black rounded-lg shadow-lg">
                <img
                    id="photo-viewer-img"
                    src=""
                    alt="Foto ítem"
                    class="object-contain w-full max-h-[80vh] bg-black"
                >
            </div>

            {{-- Botón siguiente --}}
            <button
                type="button"
                id="photo-viewer-next"
                class="absolute right-0 z-10 flex items-center justify-center w-10 h-10 mt-[45%] -translate-y-1/2 text-white bg-black/60 rounded-full hover:bg-black"
            >
                ›
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mainPhoto   = document.getElementById('main-photo');
            const thumbs      = Array.from(document.querySelectorAll('[data-photo-thumb]'));
            const viewer      = document.getElementById('photo-viewer');
            const viewerImg   = document.getElementById('photo-viewer-img');
            const btnClose    = document.getElementById('photo-viewer-close');
            const btnPrev     = document.getElementById('photo-viewer-prev');
            const btnNext     = document.getElementById('photo-viewer-next');

            if (!mainPhoto || !viewer) return;

            // índice inicial: el que tenga el borde azul, si existe
            let currentIndex = 0;
            const selectedIndex = thumbs.findIndex(t => t.classList.contains('ring-2'));
            if (selectedIndex >= 0) currentIndex = selectedIndex;

            const openViewer = (index) => {
                if (!thumbs[index]) return;

                currentIndex = index;
                viewerImg.src = thumbs[currentIndex].dataset.full;

                viewer.classList.remove('hidden');
                viewer.classList.add('flex');
            };

            const closeViewer = () => {
                viewer.classList.add('hidden');
                viewer.classList.remove('flex');
            };

            const showPrev = () => {
                if (!thumbs.length) return;
                currentIndex = (currentIndex - 1 + thumbs.length) % thumbs.length;
                viewerImg.src = thumbs[currentIndex].dataset.full;
            };

            const showNext = () => {
                if (!thumbs.length) return;
                currentIndex = (currentIndex + 1) % thumbs.length;
                viewerImg.src = thumbs[currentIndex].dataset.full;
            };

            // Click en miniaturas: cambiar foto principal y preparar viewer
            thumbs.forEach((thumb, idx) => {
                thumb.addEventListener('click', () => {
                    const full = thumb.dataset.full;
                    if (!full) return;

                    // Cambia imagen principal
                    mainPhoto.src = full;

                    // Actualiza índice para el viewer
                    currentIndex = idx;

                    // Actualiza borde de selección
                    thumbs.forEach(t => t.classList.remove('ring-2', 'ring-blue-500'));
                    thumb.classList.add('ring-2', 'ring-blue-500');
                });

                if (!thumb.dataset.index) thumb.dataset.index = idx;
            });

            // Click en la foto principal: abrir visor
            mainPhoto.addEventListener('click', () => {
                if (!thumbs.length) return; // si no hay thumbs, no hay carrusel
                openViewer(currentIndex);
            });

            // Controles visor
            btnClose.addEventListener('click', closeViewer);
            btnPrev.addEventListener('click', showPrev);
            btnNext.addEventListener('click', showNext);

            // Cerrar haciendo click en el fondo oscuro
            viewer.addEventListener('click', (e) => {
                if (e.target === viewer) {
                    closeViewer();
                }
            });

            // Navegar con teclado (ESC, ←, →)
            document.addEventListener('keydown', (e) => {
                if (viewer.classList.contains('hidden')) return;

                if (e.key === 'Escape') closeViewer();
                if (e.key === 'ArrowLeft') showPrev();
                if (e.key === 'ArrowRight') showNext();
            });
        });
    </script>
</x-app-layout>
