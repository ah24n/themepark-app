<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Add Ferry Schedule</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-6">
            @if($errors->any())
                <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('ferry.schedules.store') }}" class="space-y-4">
                @csrf

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-600">From</label>
                        <input name="from_location" required class="w-full border rounded px-3 py-2" />
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">To</label>
                        <input name="to_location" required class="w-full border rounded px-3 py-2" />
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-600">Departure time</label>
                        <input type="datetime-local" name="departure_time" required class="w-full border rounded px-3 py-2" />
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Arrival time</label>
                        <input type="datetime-local" name="arrival_time" required class="w-full border rounded px-3 py-2" />
                    </div>
                </div>

                <div>
                    <label class="text-sm text-gray-600">Capacity</label>
                    <input type="number" name="capacity" min="1" required class="w-full border rounded px-3 py-2" />
                </div>

                <x-primary-button>Save</x-primary-button>
                <a href="{{ route('ferry.manage') }}" class="ml-3 text-sm underline">Cancel</a>
            </form>
        </div>
    </div>
</x-app-layout>