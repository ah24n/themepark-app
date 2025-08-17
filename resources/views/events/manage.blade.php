<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Manage Events</h2></x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-6">
            @if(session('status'))
                <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ $errors->first() }}</div>
            @endif

            <div class="mb-4">
                <a href="{{ route('events.create') }}" class="inline-flex items-center rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-100">+ Add Event</a>
            </div>

            @if($events->isEmpty())
                <div class="text-gray-600">No events yet.</div>
            @else
                <div class="overflow-x-auto rounded-xl border bg-white">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">Name</th>
                                <th class="px-4 py-3 text-left">Type</th>
                                <th class="px-4 py-3 text-left">Schedule</th>
                                <th class="px-4 py-3 text-left">Location</th>
                                <th class="px-4 py-3 text-left">Capacity</th>
                                <th class="px-4 py-3 text-left">Sold</th>
                                <th class="px-4 py-3 text-left">Remaining</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($events as $e)
                                <tr>
                                    <td class="px-4 py-3">{{ $e['name'] }}</td>
                                    <td class="px-4 py-3 capitalize">{{ $e['type'] }}</td>
                                    <td class="px-4 py-3">
                                        @if($e['schedule'])
                                            {{ \Carbon\Carbon::parse($e['schedule'])->format('M d, Y H:i') }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $e['location'] }}</td>
                                    <td class="px-4 py-3">{{ $e['capacity'] }}</td>
                                    <td class="px-4 py-3">{{ $e['sold'] }}</td>
                                    <td class="px-4 py-3 font-semibold">{{ $e['remaining'] }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('events.edit', $e['id']) }}" class="text-blue-600 underline mr-3">Edit</a>
                                        <form action="{{ route('events.destroy', $e['id']) }}" method="POST" class="inline" onsubmit="return confirm('Delete this event?');">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 underline">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>