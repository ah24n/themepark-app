<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Room;
use Carbon\Carbon;

class RoomBookingController extends Controller
{
    public function index()
    {
        $rooms = class_exists(\App\Models\Room::class)
            ? Room::orderBy('type')->get()
            : collect();

        return view('rooms.index', compact('rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id'   => ['required', 'integer', 'exists:rooms,id'],
            'check_in'  => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests'    => ['nullable', 'integer', 'min:1'],
        ]);

        $room = Room::findOrFail($validated['room_id']);

        // Normalize dates
        $checkIn  = Carbon::parse($validated['check_in'])->startOfDay();
        $checkOut = Carbon::parse($validated['check_out'])->startOfDay();
        $nights   = $checkIn->diffInDays($checkOut);
        if ($nights < 1) {
            return back()->withErrors('Check-out must be after check-in.');
        }

        try {
            DB::beginTransaction();

            // Atomically check and decrement availability (if the column exists)
            if (Schema::hasColumn('rooms', 'availability')) {
                // Lock row to avoid race conditions
                $row = DB::table('rooms')->where('id', $room->id)->lockForUpdate()->first();
                $avail = isset($row->availability) ? (int) $row->availability : 0;
                if ($avail <= 0) {
                    DB::rollBack();
                    return back()->withErrors('No availability left for this room.')->withInput();
                }
                DB::table('rooms')->where('id', $room->id)->update(['availability' => $avail - 1]);
            }

            // Build payload only with columns that exist in your table
            $payload = ['room_id' => (int) $room->id];

            if (Schema::hasColumn('room_bookings', 'user_id')) {
                $payload['user_id'] = $request->user()->id;
            }
            if (Schema::hasColumn('room_bookings', 'room_type')) {
                $payload['room_type'] = $room->type ?? 'Room';
            }
            if (Schema::hasColumn('room_bookings', 'check_in')) {
                $payload['check_in'] = $checkIn->toDateString();
            }
            if (Schema::hasColumn('room_bookings', 'check_out')) {
                $payload['check_out'] = $checkOut->toDateString();
            }
            if (Schema::hasColumn('room_bookings', 'guests')) {
                $payload['guests'] = (int) ($validated['guests'] ?? 1);
            }
            if (Schema::hasColumn('room_bookings', 'status')) {
                $payload['status'] = 'reserved';
            }
            if (Schema::hasColumn('room_bookings', 'booking_time')) {
                $payload['booking_time'] = now();
            }

            // Optional pricing columns
            $roomPrice = isset($room->price) ? (float) $room->price : null;
            if ($roomPrice !== null) {
                if (Schema::hasColumn('room_bookings', 'price') || Schema::hasColumn('room_bookings', 'room_price') || Schema::hasColumn('room_bookings', 'rate_per_night') || Schema::hasColumn('room_bookings', 'room_rate')) {
                    $col = Schema::hasColumn('room_bookings', 'price') ? 'price'
                        : (Schema::hasColumn('room_bookings', 'room_price') ? 'room_price'
                        : (Schema::hasColumn('room_bookings', 'rate_per_night') ? 'rate_per_night' : 'room_rate'));
                    $payload[$col] = $roomPrice;
                }
                if (Schema::hasColumn('room_bookings', 'total') || Schema::hasColumn('room_bookings', 'total_price') || Schema::hasColumn('room_bookings', 'amount')) {
                    $totalCol = Schema::hasColumn('room_bookings', 'total') ? 'total'
                        : (Schema::hasColumn('room_bookings', 'total_price') ? 'total_price' : 'amount');
                    $payload[$totalCol] = $roomPrice * $nights;
                }
            }

            // Timestamps if present
            if (Schema::hasColumn('room_bookings', 'created_at')) {
                $payload['created_at'] = now();
            }
            if (Schema::hasColumn('room_bookings', 'updated_at')) {
                $payload['updated_at'] = now();
            }

            DB::table('room_bookings')->insert($payload);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Could not complete reservation. ' . $e->getMessage())->withInput();
        }

        return back()->with('status', 'Room reserved!');
    }

    public function destroy(Request $request, $bookingId)
    {
        try {
            DB::beginTransaction();

            // Fetch booking (and ensure it's the logged-in user's booking if the column exists)
            $bookingQuery = DB::table('room_bookings')->where('id', $bookingId)->lockForUpdate();
            if (Schema::hasColumn('room_bookings', 'user_id')) {
                $bookingQuery->where('user_id', $request->user()->id);
            }
            $booking = $bookingQuery->first();

            if (! $booking) {
                DB::rollBack();
                return back()->withErrors('Booking not found.');
            }

            // Only return availability once
            $wasCancelled = (Schema::hasColumn('room_bookings', 'status') && isset($booking->status) && $booking->status === 'cancelled');

            if (! $wasCancelled && Schema::hasColumn('rooms', 'availability') && isset($booking->room_id)) {
                // Lock the room row and increment availability
                $roomRow = DB::table('rooms')->where('id', $booking->room_id)->lockForUpdate()->first();
                $currentAvail = isset($roomRow->availability) ? (int) $roomRow->availability : 0;
                DB::table('rooms')->where('id', $booking->room_id)->update(['availability' => $currentAvail + 1]);
            }

            // Cancel or delete the booking
            if (Schema::hasColumn('room_bookings', 'status')) {
                DB::table('room_bookings')->where('id', $bookingId)->update(['status' => 'cancelled', 'updated_at' => now()]);
            } else {
                DB::table('room_bookings')->where('id', $bookingId)->delete();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Could not cancel booking. ' . $e->getMessage());
        }

        return back()->with('status', 'Booking cancelled');
    }
}
