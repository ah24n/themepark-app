<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Admin — All Bookings</h2></x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-6 space-y-8">

            @if(session('status'))
                <div class="p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="p-3 rounded bg-red-100 text-red-800">{{ $errors->first() }}</div>
            @endif

            {{-- Rooms --}}
            <div class="bg-white border rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b font-semibold">Room Bookings</div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">ID</th>
                                <th class="px-4 py-2 text-left">User</th>
                                <th class="px-4 py-2 text-left">Room Type</th>
                                <th class="px-4 py-2 text-left">Check-in</th>
                                <th class="px-4 py-2 text-left">Check-out</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($roomBookings as $b)
                                <tr>
                                    <td class="px-4 py-2">{{ $b->id }}</td>
                                    <td class="px-4 py-2">{{ $b->user_name }}</td>
                                    <td class="px-4 py-2">{{ $b->room_type ?? '—' }}</td>
                                    <td class="px-4 py-2">{{ $b->check_in }}</td>
                                    <td class="px-4 py-2">{{ $b->check_out }}</td>
                                    <td class="px-4 py-2 capitalize">{{ $b->status ?? '—' }}</td>
                                    <td class="px-4 py-2 text-right">
                                        @if(($b->status ?? '') !== 'cancelled')
                                            <form method="POST" action="{{ route('admin.rooms.cancel', $b->id) }}" onsubmit="return confirm('Cancel this room booking?');" class="inline">
                                                @csrf @method('PATCH')
                                                <button class="text-red-600 underline">Cancel</button>
                                            </form>
                                        @else
                                            <span class="text-gray-400">Cancelled</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-3 text-gray-500">No room bookings.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Events --}}
            <div class="bg-white border rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b font-semibold">Event Tickets</div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">ID</th>
                                <th class="px-4 py-2 text-left">User</th>
                                <th class="px-4 py-2 text-left">Event</th>
                                <th class="px-4 py-2 text-left">Schedule</th>
                                <th class="px-4 py-2 text-left">Qty</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($eventTickets as $t)
                                <tr>
                                    <td class="px-4 py-2">{{ $t->id }}</td>
                                    <td class="px-4 py-2">{{ $t->user_name }}</td>
                                    <td class="px-4 py-2">{{ $t->event_name ?? '—' }}</td>
                                    <td class="px-4 py-2">{{ $t->schedule }}</td>
                                    <td class="px-4 py-2">{{ $t->quantity ?? 1 }}</td>
                                    <td class="px-4 py-2 capitalize">{{ $t->status ?? '—' }}</td>
                                    <td class="px-4 py-2 text-right">
                                        @if(($t->status ?? '') !== 'cancelled')
                                            <form method="POST" action="{{ route('admin.events.cancel', $t->id) }}" onsubmit="return confirm('Cancel this event ticket?');" class="inline">
                                                @csrf @method('PATCH')
                                                <button class="text-red-600 underline">Cancel</button>
                                            </form>
                                        @else
                                            <span class="text-gray-400">Cancelled</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-3 text-gray-500">No event tickets.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Ferries --}}
            <div class="bg-white border rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b font-semibold">Ferry Tickets</div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">ID</th>
                                <th class="px-4 py-2 text-left">User</th>
                                <th class="px-4 py-2 text-left">Route</th>
                                <th class="px-4 py-2 text-left">Departs</th>
                                <th class="px-4 py-2 text-left">Arrives</th>
                                <th class="px-4 py-2 text-left">Qty</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($ferryTickets as $f)
                                <tr>
                                    <td class="px-4 py-2">{{ $f->id }}</td>
                                    <td class="px-4 py-2">{{ $f->user_name }}</td>
                                    <td class="px-4 py-2">{{ trim(($f->from_loc ?? '').' → '.($f->to_loc ?? '')) }}</td>
                                    <td class="px-4 py-2">{{ $f->departure_time }}</td>
                                    <td class="px-4 py-2">{{ $f->arrival_time }}</td>
                                    <td class="px-4 py-2">{{ $f->quantity ?? 1 }}</td>
                                    <td class="px-4 py-2 capitalize">{{ $f->status ?? '—' }}</td>
                                    <td class="px-4 py-2 text-right">
                                        @if(($f->status ?? '') !== 'cancelled')
                                            <form method="POST" action="{{ route('admin.ferry.cancel', $f->id) }}" onsubmit="return confirm('Cancel this ferry ticket?');" class="inline">
                                                @csrf @method('PATCH')
                                                <button class="text-red-600 underline">Cancel</button>
                                            </form>
                                        @else
                                            <span class="text-gray-400">Cancelled</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-4 py-3 text-gray-500">No ferry tickets.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>