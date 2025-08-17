<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Ferry Schedules</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-6">
            @php
                $isFerryOwner = auth()->check() && auth()->user()->email === 'ferryowner@test.com';
            @endphp

            {{-- flashes (single block) --}}
            @if(session('status'))
                <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ $errors->first() }}</div>
            @endif

            {{-- Owner toolbar --}}
            @if ($isFerryOwner)
                <div class="mb-4 flex items-center gap-2">
                    @can('manage-ferry')
                        <a href="{{ route('ferry.schedules.create') }}"
                           class="inline-flex items-center rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-100">
                            + Add Schedule
                        </a>
                        <a href="{{ route('ferry.manage') }}"
                           class="inline-flex items-center rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-100">
                            Manage Schedules
                        </a>
                    @endcan
                </div>
            @endif

            @if($schedules->isEmpty())
                <div class="text-gray-600">No ferry schedules available yet.</div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($schedules as $fs)
                        <div class="border rounded-xl bg-white overflow-hidden">
                            <div class="p-4 space-y-1">
                                <div class="font-semibold text-lg">{{ $fs['route'] }}</div>
                                @if($fs['departure'])
                                    <div class="text-sm text-gray-500">
                                        Departs: {{ \Carbon\Carbon::parse($fs['departure'])->format('M d, Y H:i') }}
                                    </div>
                                @endif
                                @if($fs['arrival'])
                                    <div class="text-sm text-gray-500">
                                        Arrives: {{ \Carbon\Carbon::parse($fs['arrival'])->format('M d, Y H:i') }}
                                    </div>
                                @endif
                                <div class="mt-1 text-sm">
                                    Capacity: {{ $fs['capacity'] }}
                                    • Sold: {{ $fs['sold'] }}
                                    • Remaining: <span class="font-semibold">{{ $fs['remaining'] }}</span>
                                </div>
                            </div>

                            <div class="p-4 border-t">
                                @if($isFerryOwner)
                                    @can('manage-ferry')
                                        <a href="{{ route('ferry.manage') }}"
                                           class="inline-flex items-center rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-100">
                                            Manage
                                        </a>
                                    @endcan
                                @elseif($fs['remaining'] > 0)
                                    <form method="POST" action="{{ route('ferry.tickets.store', $fs['id']) }}" class="flex items-center gap-2">
                                        @csrf
                                        <label for="qty-{{ $fs['id'] }}" class="sr-only">Quantity</label>
                                        <input
                                            id="qty-{{ $fs['id'] }}"
                                            type="number"
                                            name="quantity"
                                            min="1"
                                            max="{{ $fs['remaining'] }}"
                                            value="1"
                                            class="border rounded px-2 py-1 w-24"
                                        >
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