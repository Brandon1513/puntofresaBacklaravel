@props(['variant' => 'light']) {{-- light = blanco por defecto --}}

@php
    $src = $variant === 'dark'
        ? asset('images/logo.png')      // 👈 logo NEGRO
        : asset('images/logo2.png');    // 👈 logo BLANCO
@endphp

<img
    src="{{ $src }}"
    alt="Punto Fresa"
    {{ $attributes->merge(['class' => 'w-auto h-24']) }}
>
