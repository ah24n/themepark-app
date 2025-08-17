<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    /** Overview of all bookings */
    public function index()
    {
        /* --------------------
         * Rooms
         * -------------------- */
        $roomBookings = collect();
        if (Schema::hasTable('room_bookings')) {
            // Pick whichever column your rooms table actually has
            $roomTypeExpr = "'Room'";
            if (Schema::hasColumn('rooms', 'type')) {
                $roomTypeExpr = 'r.type';
            } elseif (Schema::hasColumn('rooms', 'category')) {
                $roomTypeExpr = 'r.category';
            } elseif (Schema::hasColumn('rooms', 'room_type')) {
                $roomTypeExpr = 'r.room_type';
            }

            $roomBookings = DB::table('room_bookings as rb')
                ->leftJoin('rooms as r', 'rb.room_id', '=', 'r.id')
                ->leftJoin('users as u', 'rb.user_id', '=', 'u.id')
                ->selectRaw("
                    rb.id, rb.room_id, rb.user_id, rb.check_in, rb.check_out, rb.status,
                    {$roomTypeExpr} as room_type,
                    COALESCE(u.name, u.email) as user_name
                ")
                ->orderByDesc('rb.id')
                ->get();
        }

        /* --------------------
         * Event tickets
         * -------------------- */
        $eventTickets = collect();
        if (Schema::hasTable('theme_park_tickets')) {

            // Safe event "name" expression
            $eventNameExpr = "'Event'";
            if (Schema::hasTable('events')) {
                if (Schema::hasColumn('events', 'name')) {
                    $eventNameExpr = 'e.name';
                } elseif (Schema::hasColumn('events', 'title')) {
                    $eventNameExpr = 'e.title';
                }
            }

            // Safe schedule expression
            $scheduleExpr = 'NULL';
            if (Schema::hasTable('events')) {
                if (Schema::hasColumn('events', 'schedule')) {
                    $scheduleExpr = 'e.schedule';
                } elseif (Schema::hasColumn('events', 'start_at')) {
                    $scheduleExpr = 'e.start_at';
                } elseif (Schema::hasColumn('events', 'start_time')) {
                    $scheduleExpr = 'e.start_time';
                } elseif (Schema::hasColumn('events', 'event_date')) {
                    $scheduleExpr = 'e.event_date';
                }
            }

            $eventTickets = DB::table('theme_park_tickets as t')
                ->leftJoin('events as e', 't.event_id', '=', 'e.id')
                ->leftJoin('users as u', 't.user_id', '=', 'u.id')
                ->selectRaw("
                    t.id, t.event_id, t.user_id, t.quantity, t.status,
                    {$eventNameExpr} as event_name,
                    {$scheduleExpr} as schedule,
                    COALESCE(u.name, u.email) as user_name
                ")
                ->orderByDesc('t.id')
                ->get();
        }

        /* --------------------
         * Ferry tickets (or passes)
         * -------------------- */
        $ferryTickets = collect();
        $ferryTable = Schema::hasTable('ferry_tickets') ? 'ferry_tickets'
                   : (Schema::hasTable('ferry_passes') ? 'ferry_passes' : null);

        if ($ferryTable) {
            $fk = Schema::hasColumn($ferryTable, 'ferry_schedule_id') ? 'ferry_schedule_id'
               : (Schema::hasColumn($ferryTable, 'schedule_id') ? 'schedule_id' : null);

            // Safe qty expression
            $qtyExpr = Schema::hasColumn($ferryTable, 'quantity') ? 'ft.quantity' : '1';

            // Safe schedule fields from ferry_schedules
            $fromExpr = "'From'";
            $toExpr   = "'To'";
            $depExpr  = 'NULL';
            $arrExpr  = 'NULL';

            if (Schema::hasTable('ferry_schedules')) {
                if (Schema::hasColumn('ferry_schedules', 'from_location')) {
                    $fromExpr = 'fs.from_location';
                } elseif (Schema::hasColumn('ferry_schedules', 'from')) {
                    // reserved word, quote with backticks
                    $fromExpr = 'fs.`from`';
                }

                if (Schema::hasColumn('ferry_schedules', 'to_location')) {
                    $toExpr = 'fs.to_location';
                } elseif (Schema::hasColumn('ferry_schedules', 'to')) {
                    $toExpr = 'fs.`to`';
                }

                if (Schema::hasColumn('ferry_schedules', 'departure_time')) {
                    $depExpr = 'fs.departure_time';
                } elseif (Schema::hasColumn('ferry_schedules', 'depart_at')) {
                    $depExpr = 'fs.depart_at';
                }

                if (Schema::hasColumn('ferry_schedules', 'arrival_time')) {
                    $arrExpr = 'fs.arrival_time';
                } elseif (Schema::hasColumn('ferry_schedules', 'arrive_at')) {
                    $arrExpr = 'fs.arrive_at';
                }
            }

            $query = DB::table($ferryTable.' as ft')
                ->leftJoin('ferry_schedules as fs', function ($join) use ($fk) {
                    if ($fk) {
                        $join->on('ft.'.$fk, '=', 'fs.id');
                    }
                })
                ->leftJoin('users as u', 'ft.user_id', '=', 'u.id')
                ->selectRaw("
                    ft.id, ft.user_id, ft.status,
                    {$qtyExpr} as quantity,
                    fs.id as schedule_id,
                    {$fromExpr} as from_loc,
                    {$toExpr} as to_loc,
                    {$depExpr} as departure_time,
                    {$arrExpr} as arrival_time,
                    COALESCE(u.name, u.email) as user_name
                ")
                ->orderByDesc('ft.id');

            $ferryTickets = $query->get();
        }

        return view('admin.bookings', compact('roomBookings', 'eventTickets', 'ferryTickets', 'ferryTable'));
    }

    /** Cancel a room booking and restore availability */
    public function cancelRoom($id)
    {
        if (!Schema::hasTable('room_bookings')) return back()->withErrors('room_bookings not found');

        try {
            DB::beginTransaction();

            $booking = DB::table('room_bookings')->lockForUpdate()->where('id', $id)->first();
            if (!$booking) { DB::rollBack(); return back()->withErrors('Room booking not found'); }
            if (isset($booking->status) && $booking->status === 'cancelled') {
                DB::rollBack(); return back()->with('status', 'Already cancelled.');
            }

            // Cancel booking
            $update = ['updated_at' => now()];
            if (Schema::hasColumn('room_bookings','status')) $update['status'] = 'cancelled';
            DB::table('room_bookings')->where('id', $id)->update($update);

            // Increment room availability
            if (Schema::hasTable('rooms')) {
                $availabilityCol = Schema::hasColumn('rooms','availability') ? 'availability'
                                 : (Schema::hasColumn('rooms','available') ? 'available'
                                 : (Schema::hasColumn('rooms','stock') ? 'stock' : null));
                if ($availabilityCol) {
                    DB::table('rooms')->where('id', $booking->room_id)->update([
                        $availabilityCol => DB::raw("$availabilityCol + 1"),
                        'updated_at'     => now(),
                    ]);
                }
            }

            DB::commit();
            return back()->with('status', 'Room booking cancelled.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Cancel failed: '.$e->getMessage());
        }
    }

    /** Cancel an event ticket (ThemeParkTicket) */
    public function cancelEvent($id)
    {
        if (!Schema::hasTable('theme_ark_tickets') && !Schema::hasTable('theme_park_tickets')) {
            return back()->withErrors('theme_park_tickets not found');
        }

        try {
            DB::beginTransaction();

            $table = Schema::hasTable('theme_park_tickets') ? 'theme_park_tickets' : 'theme_ark_tickets';
            $ticket = DB::table($table)->lockForUpdate()->where('id', $id)->first();
            if (!$ticket) { DB::rollBack(); return back()->withErrors('Event ticket not found'); }
            if (isset($ticket->status) && $ticket->status === 'cancelled') {
                DB::rollBack(); return back()->with('status', 'Already cancelled.');
            }

            DB::table($table)->where('id', $id)->update([
                'status'     => 'cancelled',
                'updated_at' => now(),
            ]);

            DB::commit();
            return back()->with('status', 'Event ticket cancelled.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Cancel failed: '.$e->getMessage());
        }
    }

    /** Cancel a ferry ticket/pass */
    public function cancelFerry($id)
    {
        $table = Schema::hasTable('ferry_tickets') ? 'ferry_tickets' : (Schema::hasTable('ferry_passes') ? 'ferry_passes' : null);
        if (!$table) return back()->withErrors('No ferry ticket table found');

        try {
            DB::beginTransaction();

            $ticket = DB::table($table)->lockForUpdate()->where('id', $id)->first();
            if (!$ticket) { DB::rollBack(); return back()->withErrors('Ferry ticket not found'); }
            if (isset($ticket->status) && $ticket->status === 'cancelled') {
                DB::rollBack(); return back()->with('status', 'Already cancelled.');
            }

            DB::table($table)->where('id', $id)->update([
                'status'     => 'cancelled',
                'updated_at' => now(),
            ]);

            DB::commit();
            return back()->with('status', 'Ferry ticket cancelled.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Cancel failed: '.$e->getMessage());
        }
    }
}