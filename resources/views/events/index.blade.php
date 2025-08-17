<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Events</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-6">
            @php
                $isEventOwner = auth()->check() && strcasecmp(auth()->user()->email, 'eventowner@test.com') === 0;
            @endphp

            @if($isEventOwner)
                @can('manage-events')
                    @if (Route::has('events.manage'))
                        <div class="mb-4">
                            <a href="{{ route('events.manage') }}"
                               class="inline-flex items-center rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-100">
                                Manage Events
                            </a>
                        </div>
                    @endif
                @endcan
            @endif

            @if(session('status'))
                <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ $errors->first() }}</div>
            @endif

            @if($events->isEmpty())
                <div class="text-gray-600">No events available yet.</div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($events as $e)
                        @php
                            // Remaining capacity
                            $remaining = max(0, ($e->capacity ?? 0) - ($e->sold_qty ?? 0));

                            // Image source priority: explicit field -> name-based defaults -> generic fallback
                            $nameKey = strtolower(trim($e->name ?? ''));
                            $defaults = [
                                'scuba diving'      => 'images/events/scuba.jpeg',
                                'jet ski'           => 'images/events/jetski.jpg',
                                'banana boat ride'  => 'images/events/banana.jpg',
                            ];
                            $fallback = 'images/events/default.jpg';
                            $imgRel = $e->image_url ?? $e->image_path ?? ($defaults[$nameKey] ?? $fallback);
                            $imgSrc = \Illuminate\Support\Str::startsWith($imgRel, ['http://','https://']) ? $imgRel : asset($imgRel);
                        @endphp

                        <div class="border rounded-xl bg-white overflow-hidden">
                            <div class="aspect-[16/9] bg-gray-100">
                                <img src="{{ $imgSrc }}" alt="{{ $e->name }}" class="w-full h-full object-cover">
                            </div>

                            <div class="p-4 space-y-1">
                                <div class="font-semibold text-lg">{{ $e->name }}</div>
                                <div class="text-sm text-gray-500">
                                    {{ ucfirst($e->type) }} • {{ $e->location }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($e->schedule)->format('M d, Y H:i') }}
                                </div>
                                <div class="text-sm">
                                    Capacity: {{ $e->capacity }} • Remaining: {{ $remaining }}
                                </div>
                            </div>

                            <div class="p-4 border-t">
                                @if ($isEventOwner)
                                    {{-- Owner: no reserve form --}}
                                    @can('manage-events')
                                        @if (Route::has('events.manage'))
                                            <a href="{{ route('events.manage') }}"
                                               class="inline-flex items-center rounded-lg border px-3 py-1.5 text-sm font-semibold hover:bg-gray-100">
                                                Manage Events
                                            </a>
                                        @endif
                                    @endcan
                                @elseif ($remaining > 0)
                                    {{-- Normal users: show reserve form --}}
                                    <form method="POST" action="{{ route('events.tickets.store', $e) }}" class="flex items-center gap-2">
                                        @csrf
                                        <input type="number" name="quantity" min="1" max="{{ $remaining }}" value="1" class="border rounded px-2 py-1 w-24">
                                        <x-primary-button>Reserve</x-primary-button>
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