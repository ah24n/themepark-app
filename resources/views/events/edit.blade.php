<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Edit Event</h2></x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-6">
            @if($errors->any())
                <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ $errors->first() }}</div>
            @endif

            @php
                $name = $event->name ?? $event->title ?? 'Event';
                $when = $event->schedule ?? $event->start_at ?? $event->start_time ?? $event->event_date ?? null;
                $capacity = $event->capacity ?? $event->seats ?? null;
            @endphp

            <form method="POST" action="{{ route('events.update', $event->id) }}" class="space-y-4">
                @csrf @method('PATCH')

                <div>
                    <label class="text-sm text-gray-600">Name</label>
                    <input name="name" value="{{ $name }}" required class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label class="text-sm text-gray-600">Type</label>
                    <input name="type" value="{{ $event->type ?? $event->category }}" class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label class="text-sm text-gray-600">Schedule</label>
                    <input type="datetime-local" name="schedule" value="{{ $when ? \Carbon\Carbon::parse($when)->format('Y-m-d\TH:i') : '' }}" required class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label class="text-sm text-gray-600">Location</label>
                    <input name="location" value="{{ $event->location ?? $event->venue }}" class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label class="text-sm text-gray-600">Capacity</label>
                    <input type="number" name="capacity" min="1" value="{{ $capacity }}" required class="w-full border rounded px-3 py-2" />
                </div>

                <x-primary-button>Update</x-primary-button>
                <a href="{{ route('events.manage') }}" class="ml-3 text-sm underline">Cancel</a>
            </form>
        </div>
    </div>
</x-app-layout>