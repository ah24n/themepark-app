<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased text-gray-900">
        <div class="min-h-screen bg-gradient-to-b from-sky-50 to-white flex flex-col">
            <!-- Header -->
            <header class="w-full">
                <div class="mx-auto max-w-7xl px-6 py-4 flex items-center justify-between">
                    <a href="{{ route('home') }}" class="text-xl font-extrabold tracking-tight">
                        {{ config('app.name', 'ThemePark') }}
                    </a>
                    @if (Route::has('login'))
                        <nav class="-mx-3 flex gap-2">
                            @auth
                                <a href="{{ route('dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium hover:bg-gray-100">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-sm font-medium hover:bg-gray-100">Log in</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="rounded-md px-3 py-2 text-sm font-medium hover:bg-gray-100">Register</a>
                                @endif
                            @endauth
                        </nav>
                    @endif
                </div>
            </header>

            <!-- Main -->
            <main class="flex-1">
                <!-- Hero -->
                <section class="mx-auto max-w-7xl px-6 py-12 grid gap-10 md:grid-cols-2 items-center">
                    <div>
                        <h1 class="text-4xl md:text-5xl font-extrabold leading-tight">
                            Welcome to {{ config('app.name', 'XXXXX') }} Theme Park!
                        </h1>
                        <p class="mt-4 text-lg text-gray-600">
                            Thrilling rides, dazzling shows, and unforgettable family memories — all in one place.
                            Book ferry transfers, reserve rooms, and grab your event tickets in a few clicks.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            @if (Route::has('events.index'))
                                <a href="{{ route('events.index') }}" class="inline-flex items-center rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-100">
                                    Explore Events
                                </a>
                            @endif
                            @if (Route::has('rooms.index'))
                                <a href="{{ route('rooms.index') }}" class="inline-flex items-center rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-100">
                                    Book a Room
                                </a>
                            @endif
                            @if (Route::has('ferry.index'))
                                <a href="{{ route('ferry.index') }}" class="inline-flex items-center rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-100">
                                    Ferry Schedules
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="rounded-2xl overflow-hidden shadow-xl ring-1 ring-black/5">
                        <img src="https://visitmaldives.s3.amazonaws.com/kobdmEY8/c/4yvh1rdl-shareable_image.jpg" alt="Faru Picnic Isle Theme Park Overhead" class="w-full h-full object-cover">
                    </div>
                </section>

                <!-- Gallery -->
                <section class="mx-auto max-w-7xl px-6 pb-16">
                    <h2 class="text-2xl font-semibold mb-6">A glimpse of the fun</h2>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="rounded-xl overflow-hidden shadow ring-1 ring-black/5">
                            <img src="https://medhufushiisland.com/wp-content/uploads/2018/12/water.jpg" alt="Jet Ski Events" class="w-full h-56 object-cover">
                        </div>
                        <div class="rounded-xl overflow-hidden shadow ring-1 ring-black/5">
                            <img src="https://filitheyoresort.com/wp-content/uploads/2019/03/Filitheyo_Diving1.jpg" alt="Scuba Diving Events" class="w-full h-56 object-cover">
                        </div>
                        <div class="rounded-xl overflow-hidden shadow ring-1 ring-black/5">
                            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTiJs9rNVSzh9mME6GJd_dmRRe7kQ_wBEm0KSzRPmi-sdC2Q4ON3nsPi9D4kg1T1Chjxok&usqp=CAU" alt="Villas" class="w-full h-56 object-cover">
                        </div>
                    </div>
                </section>
            </main>

            <!-- Footer -->
            <footer class="border-t">
                <div class="mx-auto max-w-7xl px-6 py-6 text-sm text-gray-500 flex items-center justify-between">
                    <span>© {{ date('Y') }} {{ config('app.name', 'ThemePark') }}</span>
                    <span>Made with Laravel & Breeze</span>
                </div>
            </footer>
        </div>
    </body>
</html>
