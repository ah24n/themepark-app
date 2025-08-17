<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Manage Rooms</h2></x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-6">
            @if(session('status'))
                <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ $errors->first() }}</div>
            @endif

            @if($rooms->isEmpty())
                <div class="text-gray-600">No rooms found.</div>
            @else
                <div class="overflow-x-auto rounded-xl border bg-white">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">Name</th>
                                <th class="px-4 py-3 text-left">Category</th>
                                <th class="px-4 py-3 text-left">Availability</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($rooms as $r)
                                <tr>
                                    <td class="px-4 py-3">{{ $r->name }}</td>
                                    <td class="px-4 py-3 capitalize">{{ $r->category }}</td>
                                    <td class="px-4 py-3">{{ $r->availability }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('rooms.availability.edit', $r->id) }}"
                                           class="text-blue-600 underline">Edit availability</a>
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