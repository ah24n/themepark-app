<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Add Event</h2></x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-6">
            @if($errors->any())
                <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('events.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-sm text-gray-600">Name</label>
                    <input name="name" required class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label class="text-sm text-gray-600">Type (e.g., parade, ride, show)</label>
                    <input name="type" class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label class="text-sm text-gray-600">Schedule</label>
                    <input type="datetime-local" name="schedule" required class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label class="text-sm text-gray-600">Location</label>
                    <input name="location" class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label class="text-sm text-gray-600">Capacity</label>
                    <input type="number" name="capacity" min="1" required class="w-full border rounded px-3 py-2" />
                </div>

                <x-primary-button>Save</x-primary-button>
                <a href="{{ route('events.manage') }}" class="ml-3 text-sm underline">Cancel</a>
            </form>
        </div>
    </div>
</x-app-layout>