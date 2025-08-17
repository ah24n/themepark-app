<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Manage Ferry Schedules</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-6">
            @if(session('status'))
                <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
            @endif

            <div class="mb-4">
                <a href="{{ route('ferry.schedules.create') }}" class="inline-flex items-center rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-100">
                    + Add Schedule
                </a>
            </div>

            @if($schedules->isEmpty())
                <div class="text-gray-600">No schedules yet.</div>
            @else
                <div class="overflow-x-auto rounded-xl border bg-white">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">Route</th>
                                <th class="px-4 py-3 text-left">Departure</th>
                                <th class="px-4 py-3 text-left">Arrival</th>
                                <th class="px-4 py-3 text-left">Capacity</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($schedules as $s)
                                <tr>
                                    <td class="px-4 py-3">
                                        {{ $s->from_location }} → {{ $s->to_location }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ \Carbon\Carbon::parse($s->departure_time)->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ \Carbon\Carbon::parse($s->arrival_time)->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3">{{ $s->capacity }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('ferry.schedules.edit', $s->id) }}" class="text-blue-600 underline mr-3">Edit</a>
                                        <form action="{{ route('ferry.schedules.destroy', $s->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this schedule?');">
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