<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto px-6 space-y-10">

            <!-- Hero / Greeting -->
            <section class="grid gap-8 md:grid-cols-2 items-center">
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold leading-tight">
                        Welcome back, {{ Auth::user()->name ?? 'Explorer' }}!
                    </h1>
                    <p class="mt-3 text-gray-600">
                        Manage your visits to <span class="font-semibold">{{ config('app.name', 'Faru Picnic Isle') }}</span>:
                        book event tickets, reserve rooms, and plan your ferry transfers — all in one place.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        @if (Route::has('events.index'))
                            <a href="{{ route('events.index') }}" class="inline-flex items-center rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-100">
                                Book Event Tickets
                            </a>
                        @endif
                        @if (Route::has('rooms.index'))
                            <a href="{{ route('rooms.index') }}" class="inline-flex items-center rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-100">
                                Reserve a Room
                            </a>
                        @endif
                        @if (Route::has('ferry.schedules'))
                            <a href="{{ route('ferry.schedules') }}" class="inline-flex items-center rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-100">
                                View Ferry Schedules
                            </a>
                        @endif
                    </div>
                </div>
                <div class="rounded-2xl overflow-hidden shadow-xl ring-1 ring-black/5 h-64 md:h-72 lg:h-80">
                    <img src="https://www.traveldailymedia.com/assets/2018/10/Image-1.jpg" alt="Ferry Picture - Dashboard" class="w-full h-full object-cover">
                </div>
            </section>

            <!-- Quick Stats -->
            <section>
                <h2 class="text-lg font-semibold mb-4">Quick stats</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="p-4 rounded-xl border bg-white">
                        <div class="text-sm text-gray-500">Upcoming Events</div>
                        <div class="mt-1 text-2xl font-bold">
                            {{ \App\Models\Event::where('schedule', '>=', now())->count() ?? '0' }}
                        </div>
                    </div>
                    <div class="p-4 rounded-xl border bg-white">
                        <div class="text-sm text-gray-500">Room Types</div>
                        <div class="mt-1 text-2xl font-bold">
                            {{ \App\Models\Room::count() ?? '0' }}
                        </div>
                    </div>
                    <div class="p-4 rounded-xl border bg-white">
                        <div class="text-sm text-gray-500">Ferry Routes</div>
                        <div class="mt-1 text-2xl font-bold">
                            {{ \App\Models\FerrySchedule::count() ?? '0' }}
                        </div>
                    </div>
                    <div class="p-4 rounded-xl border bg-white">
                        <div class="text-sm text-gray-500">Your Room Bookings</div>
                        <div class="mt-1 text-2xl font-bold">
                            @php
                                $roomBookingCount = 0;
                                if (auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('room_bookings')) {
                                    $roomBookingCount = \Illuminate\Support\Facades\DB::table('room_bookings')->where('user_id', auth()->id())->count();
                                }
                            @endphp
                            {{ $roomBookingCount }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- Upcoming Events & Your Activity -->
            <section class="grid gap-6 lg:grid-cols-2">
                <!-- Upcoming Events -->
                <div class="rounded-xl border bg-white overflow-hidden">
                    <div class="px-5 py-4 border-b">
                        <h3 class="font-semibold">Upcoming events</h3>
                    </div>
                    <div class="p-5">
                        @php
                            $upcoming = class_exists(\App\Models\Event::class)
                                ? \App\Models\Event::where('schedule', '>=', now())->orderBy('schedule')->limit(5)->get()
                                : collect();
                        @endphp
                        @if($upcoming->isEmpty())
                            <div class="text-sm text-gray-500">No upcoming events yet.</div>
                        @else
                            <ul class="divide-y">
                                @foreach($upcoming as $e)
                                    <li class="py-3 flex items-start justify-between gap-4">
                                        <div>
                                            <div class="font-medium">{{ $e->name }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ $e->location }} • {{ \Carbon\Carbon::parse($e->schedule)->format('M d, Y H:i') }}
                                            </div>
                                        </div>
                                        @if (Route::has('events.index'))
                                            <a href="{{ route('events.index') }}" class="text-sm underline">Details</a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <!-- Your recent tickets -->
                <div class="rounded-xl border bg-white overflow-hidden">
                    <div class="px-5 py-4 border-b">
                        <h3 class="font-semibold">Your recent tickets</h3>
                    </div>
                    <div class="p-5">
                        @php
                            $myTickets = collect();
                            if (auth()->check() && class_exists(\App\Models\ThemeParkTicket::class)) {
                                $myTickets = \App\Models\ThemeParkTicket::with('event')
                                    ->where('user_id', auth()->id())
                                    ->latest()
                                    ->limit(5)
                                    ->get();
                            }
                        @endphp
                        @if($myTickets->isEmpty())
                            <div class="text-sm text-gray-500">You haven’t booked any tickets yet.</div>
                        @else
                            <ul class="divide-y">
                                @foreach($myTickets as $t)
                                    <li class="py-3 flex items-start justify-between gap-4">
                                        <div>
                                            <div class="font-medium">
                                                {{ optional($t->event)->name ?? 'Event' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Qty: {{ $t->quantity }} • Status: {{ ucfirst($t->status) }}
                                            </div>
                                        </div>
                                        @if (Route::has('events.index'))
                                            <a href="{{ route('events.index') }}" class="text-sm underline">View</a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </section>

            <!-- Your room bookings -->
            <section>
                <div class="rounded-xl border bg-white overflow-hidden">
                    <div class="px-5 py-4 border-b">
                        <h3 class="font-semibold">Your room bookings</h3>
                    </div>
                    <div class="p-5">
                        @php
                            $myRooms = collect();
                            $roomNamesById = [];
                            if (auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('room_bookings')) {
                                $myRooms = \Illuminate\Support\Facades\DB::table('room_bookings')
                                    ->where('user_id', auth()->id())
                                    ->orderByDesc('id')
                                    ->limit(5)
                                    ->get();
                            }
                            if (\Illuminate\Support\Facades\Schema::hasTable('rooms')) {
                                $roomNamesById = \Illuminate\Support\Facades\DB::table('rooms')->pluck('type', 'id')->toArray();
                            }
                        @endphp

                        @if($myRooms->isEmpty())
                            <div class="text-sm text-gray-500">You haven’t reserved any rooms yet.</div>
                        @else
                            <ul class="divide-y">
                                @foreach($myRooms as $b)
                                    @php
                                        $title = $b->room_type ?? ($roomNamesById[$b->room_id] ?? 'Room');
                                        $dates = null;
                                        if (isset($b->check_in) || isset($b->check_out)) {
                                            $start = isset($b->check_in) ? \Carbon\Carbon::parse($b->check_in)->format('M d') : '?';
                                            $end   = isset($b->check_out) ? \Carbon\Carbon::parse($b->check_out)->format('M d') : '?';
                                            $dates = $start . ' – ' . $end;
                                        }
                                    @endphp
                                    <li class="py-3 flex items-start justify-between gap-4">
                                        <div>
                                            <div class="font-medium">{{ $title }}</div>
                                            <div class="text-sm text-gray-500">
                                                @if($dates) {{ $dates }} • @endif
                                                Status: {{ isset($b->status) ? ucfirst($b->status) : 'Reserved' }}
                                            </div>
                                        </div>
                                        
<div class="flex items-center gap-2">
    @if (Route::has('rooms.index'))
        <a href="{{ route('rooms.index') }}" class="text-sm underline">Manage</a>
    @endif

    @if (Route::has('rooms.cancel') && (!isset($b->status) || $b->status !== 'cancelled'))
        <form method="POST" action="{{ route('rooms.cancel', $b->id) }}" onsubmit="return confirm('Cancel this booking?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm text-red-600 underline">Cancel</button>
        </form>
    @endif
</div>
</li>

                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </section>

        </div>
    </div>
</x-app-layout>
