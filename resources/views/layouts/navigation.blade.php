<nav x-data="{ open: false }" class="bg-gray-900 border-b border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex items-center shrink-0">
                    <a href="{{ route('dashboard') }}">
                       <x-application-logo class="block w-auto h-10 lg:h-12" />

                    </a>
                </div>

                <!-- Navigation Links (solo usuarios logueados) -->
                @if (Auth::check())
                    <div class="hidden space-x-8 sm:flex sm:items-center sm:ms-6">
                        {{-- Dashboard --}}
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-white hover:text-gray-300">
                            {{ __('Dashboard') }}
                        </x-nav-link>

                        {{-- GASTOS --}}
                        @if (Auth::user()->can('gastos.ver') || Auth::user()->can('gastos.crear'))
                            <div class="relative">
                                <x-dropdown align="left">
                                    <x-slot name="trigger">
                                        <button class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-white transition duration-150 ease-in-out bg-transparent border border-transparent rounded-md hover:text-gray-300 focus:outline-none">
                                            <div>{{ __('Gastos') }}</div>
                                            <div class="ms-1">
                                                <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        <x-dropdown-link
                                            :href="route('expenses.index')"
                                            :active="request()->routeIs('expenses.index')"
                                            class="text-gray-700 hover:bg-gray-200">
                                            {{ __('Mis gastos') }}
                                        </x-dropdown-link>

                                        @if (Auth::user()->can('gastos.crear'))
                                            <x-dropdown-link
                                                :href="route('expenses.create')"
                                                :active="request()->routeIs('expenses.create')"
                                                class="text-gray-700 hover:bg-gray-200">
                                                {{ __('Registrar gasto') }}
                                            </x-dropdown-link>
                                        @endif
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        @endif

                        {{-- CAJA (caja chica + caja mostrador) --}}
                        @if (Auth::user()->hasAnyRole(['superadmin','administrador','finanzas','ventas']))
                            <div class="relative">
                                <x-dropdown align="left">
                                    <x-slot name="trigger">
                                        <button class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-white transition duration-150 ease-in-out bg-transparent border border-transparent rounded-md hover:text-gray-300 focus:outline-none">
                                            <div>{{ __('Caja') }}</div>
                                            <div class="ms-1">
                                                <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        @if (Auth::user()->hasAnyRole(['superadmin','administrador','finanzas']))
                                            <x-dropdown-link
                                                :href="route('petty-cash-sessions.index')"
                                                :active="request()->routeIs('petty-cash-sessions.*')"
                                                class="text-gray-700 hover:bg-gray-200">
                                                {{ __('Caja chica') }}
                                            </x-dropdown-link>
                                        @endif

                                        {{-- Caja mostrador / cash --}}
                                        <x-dropdown-link
                                            :href="route('petty-cash-sessions.create')"
                                            :active="request()->routeIs('petty-cash-sessions.*')"
                                            class="text-gray-700 hover:bg-gray-200">
                                            {{ __('Abrir Caja') }}
                                        </x-dropdown-link>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        @endif

                        {{-- CATÁLOGOS (gastos + items) --}}
                        @if (Auth::user()->hasAnyRole(['superadmin','administrador','finanzas']))
                            <div class="relative">
                                <x-dropdown align="left">
                                    <x-slot name="trigger">
                                        <button class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-white transition duration-150 ease-in-out bg-transparent border border-transparent rounded-md hover:text-gray-300 focus:outline-none">
                                            <div>{{ __('Catálogos') }}</div>
                                            <div class="ms-1">
                                                <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        {{-- Catálogo gastos --}}
                                        <x-dropdown-link
                                            :href="route('expense-categories.index')"
                                            :active="request()->routeIs('expense-categories.*')"
                                            class="text-gray-700 hover:bg-gray-200">
                                            {{ __('Categorías de gastos') }}
                                        </x-dropdown-link>

                                        <x-dropdown-link
                                            :href="route('cost-centers.index')"
                                            :active="request()->routeIs('cost-centers.*')"
                                            class="text-gray-700 hover:bg-gray-200">
                                            {{ __('Centros de costo') }}
                                        </x-dropdown-link>

                                        {{-- Catálogo items --}}
                                        <x-dropdown-link
                                            :href="route('item-categorias.index')"
                                            :active="request()->routeIs('item-categorias.*')"
                                            class="text-gray-700 hover:bg-gray-200">
                                            {{ __('Categorías de ítems') }}
                                        </x-dropdown-link>

                                        <x-dropdown-link
                                            :href="route('unidades.index')"
                                            :active="request()->routeIs('unidades.*')"
                                            class="text-gray-700 hover:bg-gray-200">
                                            {{ __('Unidades') }}
                                        </x-dropdown-link>

                                        @if (Auth::user()->hasAnyRole(['superadmin','administrador']))
                                            <x-dropdown-link
                                                :href="route('items.index')"
                                                :active="request()->routeIs('items.*')"
                                                class="text-gray-700 hover:bg-gray-200">
                                                {{ __('Catálogo de ítems') }}
                                            </x-dropdown-link>
                                            <x-dropdown-link :href="route('bundles.index')" :active="request()->routeIs('bundles.*')"
                                                            class="text-gray-700 hover:bg-gray-200">
                                                {{ __('Paquetes') }}
                                            </x-dropdown-link>
                                        @endif
                                        <x-dropdown-link :href="route('clientes.index')" :active="request()->routeIs('clientes.*')"
                                                            class="text-gray-700 hover:bg-gray-200">
                                                {{ __('Clientes') }}
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('event-orders.index')" :active="request()->routeIs('event-orders.*')"
                                                            class="text-gray-700 hover:bg-gray-200">
                                                {{ __('Ordenes de Eventos') }}
                                        </x-dropdown-link>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        @endif

                        {{-- ADMINISTRACIÓN (usuarios) --}}
                        @if (Auth::user()->hasAnyRole(['superadmin','administrador']))
                            <div class="relative">
                                <x-dropdown align="left">
                                    <x-slot name="trigger">
                                        <button class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-white transition duration-150 ease-in-out bg-transparent border border-transparent rounded-md hover:text-gray-300 focus:outline-none">
                                            <div>{{ __('Administración') }}</div>
                                            <div class="ms-1">
                                                <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        <x-dropdown-link
                                            :href="route('users.index')"
                                            :active="request()->routeIs('users.*')"
                                            class="text-gray-700 hover:bg-gray-200">
                                            {{ __('Usuarios') }}
                                        </x-dropdown-link>
                                        <x-dropdown-link
                                            :href="route('expense-categories.index')"
                                            :active="request()->routeIs('expense-categories.index')"
                                            class="text-gray-700 hover:bg-gray-200">
                                            {{ __('Categoria de Gastos') }}
                                        </x-dropdown-link>
                                        <x-dropdown-link
                                            :href="route('cost-centers.index')"
                                            :active="request()->routeIs('cost-centers.index')"
                                            class="text-gray-700 hover:bg-gray-200">
                                            {{ __('Centro de Gastos') }}
                                        </x-dropdown-link>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        @endif

                    </div>
                @endif
            </div>

            <!-- Settings Dropdown / Login -->
            @if (Auth::check())
                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-white transition duration-150 ease-in-out bg-transparent border border-transparent rounded-md hover:text-gray-300 focus:outline-none">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')" class="text-gray-700 hover:bg-gray-200">
                                {{ __('Perfil') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" class="text-gray-700 hover:bg-gray-200"
                                                 onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Salir') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            @else
                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    <a href="{{ route('login') }}" class="text-sm text-white underline">
                        {{ __('Iniciar sesión') }}
                    </a>
                </div>
            @endif

            <!-- Hamburger (móvil) -->
            <div class="flex items-center -me-2 sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 text-white transition duration-150 ease-in-out rounded-md hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500">
                    <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-white">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        @if (Auth::check())
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4">
                    <div class="text-base font-medium text-white">{{ Auth::user()->name }}</div>
                    <div class="text-sm font-medium text-gray-300">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    {{-- GASTOS --}}
                    @if (Auth::user()->can('gastos.ver') || Auth::user()->can('gastos.crear'))
                        <x-responsive-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')" class="text-white">
                            {{ __('Gastos') }}
                        </x-responsive-nav-link>
                    @endif

                    {{-- CAJA --}}
                    @if (Auth::user()->hasAnyRole(['superadmin','administrador','finanzas','ventas']))
                        @if (Auth::user()->hasAnyRole(['superadmin','administrador','finanzas']))
                            <x-responsive-nav-link :href="route('petty-cash-sessions.index')" :active="request()->routeIs('petty-cash-sessions.*')" class="text-white">
                                {{ __('Caja chica') }}
                            </x-responsive-nav-link>
                        @endif

                        
                    @endif

                    {{-- CATÁLOGOS --}}
                    @if (Auth::user()->hasAnyRole(['superadmin','administrador','finanzas']))
                        <x-responsive-nav-link :href="route('expense-categories.index')" :active="request()->routeIs('expense-categories.*')" class="text-white">
                            {{ __('Categorías de gastos') }}
                        </x-responsive-nav-link>

                        <x-responsive-nav-link :href="route('cost-centers.index')" :active="request()->routeIs('cost-centers.*')" class="text-white">
                            {{ __('Centros de costo') }}
                        </x-responsive-nav-link>

                        <x-responsive-nav-link :href="route('item-categorias.index')" :active="request()->routeIs('item-categorias.*')" class="text-white">
                            {{ __('Categorías de ítems') }}
                        </x-responsive-nav-link>

                        <x-responsive-nav-link :href="route('unidades.index')" :active="request()->routeIs('unidades.*')" class="text-white">
                            {{ __('Unidades') }}
                        </x-responsive-nav-link>

                        @if (Auth::user()->hasAnyRole(['superadmin','administrador']))
                            <x-responsive-nav-link :href="route('items.index')" :active="request()->routeIs('items.*')" class="text-white">
                                {{ __('Catálogo de ítems') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('bundles.index')" :active="request()->routeIs('bundles.*')"
                                            class="text-gray-700 hover:bg-gray-200">
                                {{ __('Paquetes') }}
                            </x-responsive-nav-link>
                        @endif
                    @endif

                    {{-- ADMINISTRACIÓN --}}
                    @if (Auth::user()->hasAnyRole(['superadmin','administrador']))
                        <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')" class="text-white">
                            {{ __('Usuarios') }}
                        </x-responsive-nav-link>
                    @endif

                    {{-- Perfil --}}
                    <x-responsive-nav-link :href="route('profile.edit')" class="text-white">
                        {{ __('Perfil') }}
                    </x-responsive-nav-link>

                    {{-- Logout --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')" class="text-white"
                                               onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Salir') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <div class="pt-4 pb-1 border-t border-gray-200">
                <x-responsive-nav-link :href="route('login')" class="text-white">
                    {{ __('Iniciar sesión') }}
                </x-responsive-nav-link>
            </div>
        @endif
    </div>
</nav>
