<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoomController extends Controller
{
    /** Public listing (unchanged, but resilient order-by) */
    public function index()
    {
        $orderCol = Schema::hasColumn('rooms', 'type') ? 'type'
                  : (Schema::hasColumn('rooms', 'category') ? 'category' : 'id');

        $rooms = Room::orderBy($orderCol)->get();
        return view('rooms.index', compact('rooms'));
    }

    /** HOTEL OWNER: list rooms with robust column detection + aliases */
    public function manage()
    {
        // Which columns actually exist?
        $nameCol = Schema::hasColumn('rooms', 'name') ? 'name'
                 : (Schema::hasColumn('rooms', 'title') ? 'title'
                 : (Schema::hasColumn('rooms', 'room_name') ? 'room_name'
                 : (Schema::hasColumn('rooms', 'number') ? 'number' : null)));

        $categoryCol = Schema::hasColumn('rooms', 'category') ? 'category'
                     : (Schema::hasColumn('rooms', 'type') ? 'type'
                     : (Schema::hasColumn('rooms', 'room_type') ? 'room_type' : null));

        $availabilityCol = Schema::hasColumn('rooms', 'availability') ? 'availability'
                        : (Schema::hasColumn('rooms', 'available') ? 'available'
                        : (Schema::hasColumn('rooms', 'stock') ? 'stock' : null));

        // Build select with aliases so the blade can use name/category/availability
        $selects = ['id'];
        $selects[] = DB::raw(($nameCol ?: 'id') . ' as name');
        $selects[] = DB::raw(($categoryCol ?: "''") . ' as category');
        $selects[] = DB::raw(($availabilityCol ?: '0') . ' as availability');

        $q = DB::table('rooms')->select($selects);
        if ($categoryCol) $q->orderBy($categoryCol);
        if ($nameCol)     $q->orderBy($nameCol);

        $rooms = $q->get();

        return view('rooms.manage', compact('rooms'));
    }

    /** HOTEL OWNER: show form to edit one room's availability */
    public function editAvailability(Room $room) // route-model binding: {room}
    {
        // Count active (non-cancelled) bookings for safety
        $q = DB::table('room_bookings')->where('room_id', $room->id);
        if (Schema::hasTable('room_bookings') && Schema::hasColumn('room_bookings', 'status')) {
            $q->where('status', '!=', 'cancelled');
        }
        $activeBookings = (int) $q->count();

        // Compute a display label if your table doesn't have "name"
        $label = $room->name
              ?? $room->title
              ?? $room->room_name
              ?? $room->number
              ?? ('Room #'.$room->id);

        return view('rooms.edit_availability', [
            'room'            => $room,
            'activeBookings'  => $activeBookings,
            'displayName'     => $label, // use in blade if $room->name is missing
        ]);
    }

    /** HOTEL OWNER: persist availability with checks + row lock */
    public function updateAvailability(Request $request, Room $room) // route-model binding: {room}
    {
        // Detect the real availability column
        $availabilityCol = Schema::hasColumn('rooms', 'availability') ? 'availability'
                        : (Schema::hasColumn('rooms', 'available') ? 'available'
                        : (Schema::hasColumn('rooms', 'stock') ? 'stock' : null));

        if (!$availabilityCol) {
            return back()->withErrors('No availability column found on rooms table.');
        }

        $data = $request->validate([
            'availability' => ['required', 'integer', 'min:0'],
        ]);

        try {
            DB::beginTransaction();

            // Lock row to avoid races
            $locked = DB::table('rooms')->where('id', $room->id)->lockForUpdate()->first();
            if (!$locked) {
                DB::rollBack();
                return redirect()->route('rooms.manage')->withErrors('Room not found.');
            }

            // Re-check active bookings while locked
            $q = DB::table('room_bookings')->where('room_id', $room->id);
            if (Schema::hasTable('room_bookings') && Schema::hasColumn('room_bookings', 'status')) {
                $q->where('status', '!=', 'cancelled');
            }
            $activeBookings = (int) $q->count();

            $newAvailability = (int) $data['availability'];
            if ($newAvailability < $activeBookings) {
                DB::rollBack();
                return back()
                    ->withErrors("Cannot set availability below current active bookings ({$activeBookings}).")
                    ->withInput();
            }

            DB::table('rooms')->where('id', $room->id)->update([
                $availabilityCol => $newAvailability,
                'updated_at'     => now(),
            ]);

            DB::commit();
            return redirect()->route('rooms.manage')->with('status', 'Availability updated.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Update failed: '.$e->getMessage())->withInput();
        }
    }
}