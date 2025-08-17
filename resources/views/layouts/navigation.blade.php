<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    @php
        $user          = auth()->user();
        $isAuth        = auth()->check();
        $isFerryOwner  = $user && strcasecmp($user->email, 'ferryowner@test.com') === 0;
        $isHotelOwner  = $user && strcasecmp($user->email, 'hotelowner@test.com') === 0;
        $isEventOwner  = $user && strcasecmp($user->email, 'eventowner@test.com') === 0;
        $isAdmin       = $user && strcasecmp($user->email, 'admin@test.com') === 0;
    @endphp

    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @if ($isAdmin)
                        {{-- Admin: Admin dashboard link --}}
                        @if (Route::has('admin.bookings'))
                            <x-nav-link :href="route('admin.bookings')" :active="request()->routeIs('admin.*')">
                                {{ __('Admin') }}
                            </x-nav-link>
                        @endif

                    @elseif ($isFerryOwner)
                        {{-- Ferry Owner: only Ferry + Manage Schedules --}}
                        @if (Route::has('ferry.schedules'))
                            <x-nav-link :href="route('ferry.schedules')" :active="request()->routeIs('ferry.schedules')">
                                {{ __('Ferry') }}
                            </x-nav-link>
                        @endif
                        @can('manage-ferry')
                            @if (Route::has('ferry.manage'))
                                <x-nav-link :href="route('ferry.manage')" :active="request()->routeIs('ferry.manage')">
                                    {{ __('Manage Schedules') }}
                                </x-nav-link>
                            @endif
                        @endcan

                    @elseif ($isHotelOwner)
                        {{-- Hotel Owner: only Rooms (+ optional Manage Rooms) --}}
                        @if (Route::has('rooms.index'))
                            <x-nav-link :href="route('rooms.index')" :active="request()->routeIs('rooms.*')">
                                {{ __('Rooms') }}
                            </x-nav-link>
                        @endif
                        @can('manage-rooms')
                            @if (Route::has('rooms.manage'))
                                <x-nav-link :href="route('rooms.manage')" :active="request()->routeIs('rooms.manage')">
                                    {{ __('Manage Rooms') }}
                                </x-nav-link>
                            @endif
                        @endcan

                    @elseif ($isEventOwner)
                        {{-- Event Owner: only Events (+ Manage Events) --}}
                        @if (Route::has('events.index'))
                            <x-nav-link :href="route('events.index')" :active="request()->routeIs('events.*')">
                                {{ __('Events') }}
                            </x-nav-link>
                        @endif
                        @can('manage-events')
                            @if (Route::has('events.manage'))
                                <x-nav-link :href="route('events.manage')" :active="request()->routeIs('events.manage')">
                                    {{ __('Manage Events') }}
                                </x-nav-link>
                            @endif
                        @endcan

                    @else
                        {{-- Logged-in regular user: NO Home tab; Guest: Home only --}}
                        @if ($isAuth)
                            @if (Route::has('events.index'))
                                <x-nav-link :href="route('events.index')" :active="request()->routeIs('events.*')">
                                    {{ __('Events') }}
                                </x-nav-link>
                            @endif
                            @if (Route::has('rooms.index'))
                                <x-nav-link :href="route('rooms.index')" :active="request()->routeIs('rooms.*')">
                                    {{ __('Rooms') }}
                                </x-nav-link>
                            @endif
                            @if (Route::has('ferry.schedules'))
                                <x-nav-link :href="route('ferry.schedules')" :active="request()->routeIs('ferry.*')">
                                    {{ __('Ferry') }}
                                </x-nav-link>
                            @endif
                            @if (Route::has('dashboard'))
                                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                                    {{ __('Dashboard') }}
                                </x-nav-link>
                            @endif
                        @else
                            @if (Route::has('home'))
                                <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                                    {{ __('Home') }}
                                </x-nav-link>
                            @endif
                        @endif
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @if (Route::has('profile.edit'))
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @if ($isAdmin)
                @if (Route::has('admin.bookings'))
                    <x-responsive-nav-link :href="route('admin.bookings')" :active="request()->routeIs('admin.*')">
                        Admin
                    </x-responsive-nav-link>
                @endif

            @elseif ($isFerryOwner)
                @if (Route::has('ferry.schedules'))
                    <x-responsive-nav-link :href="route('ferry.schedules')" :active="request()->routeIs('ferry.schedules')">Ferry</x-responsive-nav-link>
                @endif
                @can('manage-ferry')
                    @if (Route::has('ferry.manage'))
                        <x-responsive-nav-link :href="route('ferry.manage')" :active="request()->routeIs('ferry.manage')">Manage Schedules</x-responsive-nav-link>
                    @endif
                @endcan

            @elseif ($isHotelOwner)
                @if (Route::has('rooms.index'))
                    <x-responsive-nav-link :href="route('rooms.index')" :active="request()->routeIs('rooms.*')">Rooms</x-responsive-nav-link>
                @endif
                @can('manage-rooms')
                    @if (Route::has('rooms.manage'))
                        <x-responsive-nav-link :href="route('rooms.manage')" :active="request()->routeIs('rooms.manage')">Manage Rooms</x-responsive-nav-link>
                    @endif
                @endcan

            @elseif ($isEventOwner)
                @if (Route::has('events.index'))
                    <x-responsive-nav-link :href="route('events.index')" :active="request()->routeIs('events.*')">Events</x-responsive-nav-link>
                @endif
                @can('manage-events')
                    @if (Route::has('events.manage'))
                        <x-responsive-nav-link :href="route('events.manage')" :active="request()->routeIs('events.manage')">Manage Events</x-responsive-nav-link>
                    @endif
                @endcan

            @else
                @if ($isAuth)
                    @if (Route::has('events.index'))
                        <x-responsive-nav-link :href="route('events.index')" :active="request()->routeIs('events.*')">Events</x-responsive-nav-link>
                    @endif
                    @if (Route::has('rooms.index'))
                        <x-responsive-nav-link :href="route('rooms.index')" :active="request()->routeIs('rooms.*')">Rooms</x-responsive-nav-link>
                    @endif
                    @if (Route::has('ferry.schedules'))
                        <x-responsive-nav-link :href="route('ferry.schedules')" :active="request()->routeIs('ferry.*')">Ferry</x-responsive-nav-link>
                    @endif
                    @if (Route::has('dashboard'))
                        <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-responsive-nav-link>
                    @endif
                @else
                    @if (Route::has('home'))
                        <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">Home</x-responsive-nav-link>
                    @endif
                @endif
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                @if (Route::has('profile.edit'))
                    <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>