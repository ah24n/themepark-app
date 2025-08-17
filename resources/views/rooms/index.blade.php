<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Rooms</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-6">
            @php
                $isHotelOwner = auth()->check() && strcasecmp(auth()->user()->email, 'hotelowner@test.com') === 0;
            @endphp

            @if ($isHotelOwner)
                <div class="mb-4">
                    <a href="{{ route('rooms.manage') }}"
                       class="inline-flex items-center rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-100">
                        Manage Rooms
                    </a>
                </div>
            @endif

            @if(session('status'))
                <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
            @endif

            @if($rooms->isEmpty())
                <div class="text-gray-600">No rooms available yet.</div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($rooms as $r)
                        @php
                            $title = $r->type ?? $r->name ?? 'Room';
                            $typeKey = strtolower(trim($r->type ?? ''));
                            $defaults = [
                                'family suite' => 'images/rooms/family.webp',
                                'deluxe'       => 'images/rooms/deluxe.webp',
                                'standard'     => 'images/rooms/standard.webp',
                            ];
                            $fallback = 'images/rooms/default.jpg';
                            $imgRel  = $r->image_url ?? $r->image_path ?? ($defaults[$typeKey] ?? $fallback);
                            $imgSrc  = \Illuminate\Support\Str::startsWith($imgRel, ['http://','https://']) ? $imgRel : asset($imgRel);
                        @endphp

                        <div class="border rounded-xl bg-white overflow-hidden">
                            <div class="aspect-[16/9] bg-gray-100">
                                <img src="{{ $imgSrc }}" alt="{{ $title }}" class="w-full h-full object-cover">
                            </div>

                            <div class="p-4 space-y-1">
                                <div class="font-semibold text-lg">{{ $title }}</div>
                                <div class="text-sm text-gray-500">Price per night</div>
                                <div class="text-lg font-bold">
                                    @if(isset($r->price))
                                        $ {{ number_format($r->price, 2) }}
                                    @else
                                        —
                                    @endif
                                </div>
                                @if(isset($r->availability))
                                    <div class="text-sm">Available: {{ $r->availability }}</div>
                                @endif
                            </div>

                            <div class="p-4 border-t">
                                @if ($isHotelOwner)
                                    {{-- Hotel owner: no reserve form --}}
                                    @can('manage-rooms')
                                        @if (Route::has('rooms.manage'))
                                            <a href="{{ route('rooms.manage') }}"
                                               class="inline-flex items-center rounded-lg border px-3 py-1.5 text-sm font-semibold hover:bg-gray-100">
                                                Manage Rooms
                                            </a>
                                        @endif
                                    @endcan
                                @elseif (($r->availability ?? 0) > 0)
                                    {{-- Normal users: show reserve form --}}
                                    <form method="POST" action="{{ route('rooms.book') }}" class="grid grid-cols-1 gap-2">
                                        @csrf
                                        <input type="hidden" name="room_id" value="{{ $r->id }}">
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="text-xs text-gray-600">Check-in</label>
                                                <input type="date" name="check_in" class="w-full border rounded px-2 py-1" required>
                                            </div>
                                            <div>
                                                <label class="text-xs text-gray-600">Check-out</label>
                                                <input type="date" name="check_out" class="w-full border rounded px-2 py-1" required>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="text-xs text-gray-600">Guests</label>
                                                <input type="number" name="guests" min="1" value="1" class="w-full border rounded px-2 py-1" required>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <x-primary-button>Reserve</x-primary-button>
                                        </div>
                                    </form>
                                @else
                                    <div class="text-sm text-red-600">Sold out</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>