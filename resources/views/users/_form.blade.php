@php /* $user?->id existe en edit; en create será null */ @endphp

<!-- Nombre -->
<div class="mb-4">
    <x-input-label for="name" :value="__('Nombre')" />
    <x-text-input id="name" name="name" type="text" class="block w-full mt-1"
        :value="old('name', $user->name ?? '')" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<!-- Correo -->
<div class="mb-4">
    <x-input-label for="email" :value="__('Correo electrónico')" />
    <x-text-input id="email" name="email" type="email" class="block w-full mt-1"
        :value="old('email', $user->email ?? '')" required />
    <x-input-error :messages="$errors->get('email')" class="mt-2" />
</div>
<!-- Contraseña -->
<div class="mb-4">
    <x-input-label for="password" :value="__('Contraseña')" />
    <div class="relative">
        <input
            id="password"
            name="password"
            type="password"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10"
            autocomplete="new-password"
            @if(empty($user?->id)) required @endif
            @if(!empty($user?->id)) placeholder="Dejar en blanco para no cambiar" @endif
        />
        <button type="button"
                class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700 js-toggle-pass"
                aria-label="Mostrar u ocultar contraseña"
                data-target="password">
            <!-- icono ojo -->
            <svg class="h-5 w-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12z" />
                <circle cx="12" cy="12" r="3" stroke-width="1.5" />
            </svg>
        </button>
    </div>
    <x-input-error :messages="$errors->get('password')" class="mt-2" />
</div>

<!-- Confirmación -->
<div class="mb-4">
    <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" />
    <div class="relative">
        <input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10"
            autocomplete="new-password"
            @if(empty($user?->id)) required @endif
        />
        <button type="button"
                class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700 js-toggle-pass"
                aria-label="Mostrar u ocultar confirmación"
                data-target="password_confirmation">
            <svg class="h-5 w-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12z" />
                <circle cx="12" cy="12" r="3" stroke-width="1.5" />
            </svg>
        </button>
    </div>
    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
</div>

<!-- Roles (checkboxes) -->
<div class="mb-4">
    <x-input-label :value="__('Roles asignados')" />
    <div class="mt-2 space-y-2">
        @foreach ($roles as $role)
            <label class="inline-flex items-center">
                <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                    class="text-indigo-600 border-gray-300 rounded shadow-sm focus:ring-indigo-500"
                    @checked(in_array($role->id, old('roles', $selected ?? [])))>
                <span class="ml-2 text-sm text-gray-700">{{ $role->name }}</span>
            </label>
        @endforeach
    </div>
    <x-input-error :messages="$errors->get('roles')" class="mt-2" />
</div>

<!-- Estado (si agregaste la columna activo) -->
@if (Schema::hasColumn('users','activo'))

@endif

<!-- Botón -->
<div class="flex justify-end mt-6">
    <a href="{{ route('users.index') }}" class="px-3 py-2 mr-2 rounded-md border text-sm text-gray-700 hover:bg-gray-50">
        Cancelar
    </a>
    <x-primary-button>
        {{ $user?->id ? 'Actualizar usuario' : 'Crear usuario' }}
    </x-primary-button>
</div>


@once
<script>
(function () {
  if (window._pwdToggleInit) return; window._pwdToggleInit = true;
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.js-toggle-pass'); if (!btn) return;
    const id = btn.getAttribute('data-target');
    const input = document.getElementById(id); if (!input) return;
    input.type = (input.type === 'password') ? 'text' : 'password';
    // opcional: cambiar icono (simplemente alterna una clase)
    btn.classList.toggle('text-indigo-600');
  });
})();
</script>
@endonce
