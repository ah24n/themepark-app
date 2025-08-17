<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Edit Availability — {{ $room->name }}</h2></x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-6">
            @if($errors->any())
                <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ $errors->first() }}</div>
            @endif

            <div class="mb-4 text-sm text-gray-600">
                <div><span class="font-semibold">Category:</span> {{ $room->category }}</div>
                <div><span class="font-semibold">Current availability:</span> {{ $room->availability }}</div>
                <div><span class="font-semibold">Active bookings:</span> {{ $activeBookings }}</div>
            </div>

            <form method="POST" action="{{ route('rooms.availability.update', $room->id) }}" class="space-y-4">
                @csrf @method('PATCH')

                <div>
                    <label class="text-sm text-gray-600">New availability</label>
                    <input type="number" name="availability"
                           min="{{ $activeBookings }}"
                           value="{{ old('availability', $room->availability) }}"
                           class="w-full border rounded px-3 py-2" required />
                    <p class="text-xs text-gray-500 mt-1">
                        Must be at least the number of active bookings ({{ $activeBookings }}).
                    </p>
                </div>

                <x-primary-button>Save</x-primary-button>
                <a href="{{ route('rooms.manage') }}" class="ml-3 text-sm underline">Cancel</a>
            </form>
        </div>
    </div>
</x-app-layout>