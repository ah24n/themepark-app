<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class FerryController extends Controller
{
    public function index()
    {
        $schedules = collect();
        if (Schema::hasTable('ferry_schedules')) {
            $schedules = DB::table('ferry_schedules')->orderBy('id')->get();
        }

        $rows = $schedules->map(function ($s) {
            $capacity = 0;
            if (Schema::hasColumn('ferry_schedules', 'capacity') && isset($s->capacity)) {
                $capacity = (int) $s->capacity;
            } elseif (Schema::hasColumn('ferry_schedules', 'seats') && isset($s->seats)) {
                $capacity = (int) $s->seats;
            }

            $scheduleId = isset($s->id) ? (int) $s->id : null;

            $sold = $this->soldForSchedule($scheduleId);
            $remaining = max(0, $capacity - $sold);

            // Build route label from your schema first
            $route = '';
            if (isset($s->route)) {
                $route = $s->route;
            } elseif (isset($s->from_location) || isset($s->to_location)) {
                $route = trim(($s->from_location ?? '') . ' → ' . ($s->to_location ?? ''));
            } elseif (isset($s->from) || isset($s->to)) {
                $route = trim(($s->from ?? '') . ' → ' . ($s->to ?? ''));
            }

            $dep = isset($s->departure_time) ? $s->departure_time : (isset($s->depart_at) ? $s->depart_at : null);
            $arr = isset($s->arrival_time) ? $s->arrival_time : (isset($s->arrive_at) ? $s->arrive_at : null);

            return [
                'id'        => $scheduleId,
                'route'     => $route !== '' ? $route : 'Ferry',
                'departure' => $dep,
                'arrival'   => $arr,
                'capacity'  => $capacity,
                'sold'      => $sold,
                'remaining' => $remaining,
            ];
        });

        return view('ferry.schedules', ['schedules' => $rows]);
    }

    public function store(Request $request, $scheduleId)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);
        $qty = (int) $data['quantity'];

        if (!Schema::hasTable('ferry_schedules')) {
            return back()->withErrors('Ferry schedules table is missing.');
        }

        try {
            DB::beginTransaction();

            // Lock the schedule row for accurate remaining calculation
            $schedule = DB::table('ferry_schedules')->where('id', $scheduleId)->lockForUpdate()->first();
            if (!$schedule) {
                DB::rollBack();
                return back()->withErrors('Schedule not found.');
            }

            // Capacity from your columns
            $capacity = 0;
            if (Schema::hasColumn('ferry_schedules', 'capacity') && isset($schedule->capacity)) {
                $capacity = (int) $schedule->capacity;
            } elseif (Schema::hasColumn('ferry_schedules', 'seats') && isset($schedule->seats)) {
                $capacity = (int) $schedule->seats;
            }

            // Seats remaining
            $sold = $this->soldForSchedule((int) $scheduleId);
            $remaining = max(0, $capacity - $sold);

            if ($qty > $remaining) {
                DB::rollBack();
                return back()->withErrors("Only {$remaining} seats remaining.")->withInput();
            }

            // Choose tickets table (prefer ferry_tickets)
            $table = Schema::hasTable('ferry_tickets') ? 'ferry_tickets' : (Schema::hasTable('ferry_passes') ? 'ferry_passes' : null);
            if (!$table) {
                DB::rollBack();
                return back()->withErrors('No ferry tickets table found.');
            }

            // Build the insert payload using whatever columns your table has
            $payload = [];

            // Foreign key to schedule
            if (Schema::hasColumn($table, 'ferry_schedule_id')) {
                $payload['ferry_schedule_id'] = (int) $scheduleId;
            } elseif (Schema::hasColumn($table, 'schedule_id')) {
                $payload['schedule_id'] = (int) $scheduleId;
            }

            // User
            if (Schema::hasColumn($table, 'user_id')) {
                $payload['user_id'] = $request->user()->id;
            }

            // Quantity
            if (Schema::hasColumn($table, 'quantity')) {
                $payload['quantity'] = $qty;
            }

            // Status
            if (Schema::hasColumn($table, 'status')) {
                $payload['status'] = 'reserved';
            }

            // Copy schedule fields if your table stores them
            if (Schema::hasColumn($table, 'departure_time') && isset($schedule->departure_time)) {
                $payload['departure_time'] = $schedule->departure_time;
            }
            if (Schema::hasColumn($table, 'from_location') && isset($schedule->from_location)) {
                $payload['from_location'] = $schedule->from_location;
            }
            if (Schema::hasColumn($table, 'to_location') && isset($schedule->to_location)) {
                $payload['to_location'] = $schedule->to_location;
            }

            // Timestamps (if not defaulted)
            if (Schema::hasColumn($table, 'created_at')) {
                $payload['created_at'] = now();
            }
            if (Schema::hasColumn($table, 'updated_at')) {
                $payload['updated_at'] = now();
            }

            DB::table($table)->insert($payload);

            DB::commit();
            return back()->with('status', 'Ferry tickets reserved!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Could not reserve: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Sum tickets sold for the given schedule id (supports ferry_tickets/ferry_passes).
     */
    protected function soldForSchedule(int $scheduleId): int
    {
        $sold = 0;

        if (Schema::hasTable('ferry_tickets')) {
            $fk = Schema::hasColumn('ferry_tickets', 'ferry_schedule_id') ? 'ferry_schedule_id'
                : (Schema::hasColumn('ferry_tickets', 'schedule_id') ? 'schedule_id' : null);
            if ($fk) {
                $q = DB::table('ferry_tickets')->where($fk, $scheduleId);
                if (Schema::hasColumn('ferry_tickets', 'status')) {
                    $q->where('status', '!=', 'cancelled');
                }
                $sold += Schema::hasColumn('ferry_tickets', 'quantity') ? (int) $q->sum('quantity') : (int) $q->count();
            }
        }

        if (Schema::hasTable('ferry_passes')) {
            $fk = Schema::hasColumn('ferry_passes', 'ferry_schedule_id') ? 'ferry_schedule_id'
                : (Schema::hasColumn('ferry_passes', 'schedule_id') ? 'schedule_id' : null);
            if ($fk) {
                $q = DB::table('ferry_passes')->where($fk, $scheduleId);
                if (Schema::hasColumn('ferry_passes', 'status')) {
                    $q->where('status', '!=', 'cancelled');
                }
                $sold += Schema::hasColumn('ferry_passes', 'quantity') ? (int) $q->sum('quantity') : (int) $q->count();
            }
        }

        return $sold;
    }

    /** Owner: list schedules with edit/delete actions */
    public function manage()
    {
        $schedules = Schema::hasTable('ferry_schedules')
            ? DB::table('ferry_schedules')->orderByDesc('departure_time')->get()
            : collect();

        return view('ferry.manage', compact('schedules'));
    }

    /** Owner: show create form */
    public function create()
    {
        return view('ferry.create');
    }

    /** Owner: create schedule */
    public function storeSchedule(Request $request)
    {
        $data = $request->validate([
            'departure_time' => ['required', 'date'],
            'arrival_time'   => ['required', 'date', 'after:departure_time'],
            'from_location'  => ['required', 'string', 'max:255'],
            'to_location'    => ['required', 'string', 'max:255'],
            'capacity'       => ['required', 'integer', 'min:1'],
        ]);

        DB::table('ferry_schedules')->insert([
            'departure_time' => $data['departure_time'],
            'arrival_time'   => $data['arrival_time'],
            'from_location'  => $data['from_location'],
            'to_location'    => $data['to_location'],
            'capacity'       => $data['capacity'],
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return redirect()->route('ferry.manage')->with('status', 'Schedule created.');
    }

    /** Owner: show edit form */
    public function edit($id)
    {
        $schedule = DB::table('ferry_schedules')->where('id', $id)->first();
        abort_unless($schedule, 404);

        return view('ferry.edit', compact('schedule'));
    }

    /** Owner: update schedule */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'departure_time' => ['required', 'date'],
            'arrival_time'   => ['required', 'date', 'after:departure_time'],
            'from_location'  => ['required', 'string', 'max:255'],
            'to_location'    => ['required', 'string', 'max:255'],
            'capacity'       => ['required', 'integer', 'min:1'],
        ]);

        DB::table('ferry_schedules')->where('id', $id)->update([
            'departure_time' => $data['departure_time'],
            'arrival_time'   => $data['arrival_time'],
            'from_location'  => $data['from_location'],
            'to_location'    => $data['to_location'],
            'capacity'       => $data['capacity'],
            'updated_at'     => now(),
        ]);

        return redirect()->route('ferry.manage')->with('status', 'Schedule updated.');
    }

    /** Owner: delete schedule */
    public function destroySchedule($id)
    {
        DB::table('ferry_schedules')->where('id', $id)->delete();
        return redirect()->route('ferry.manage')->with('status', 'Schedule deleted.');
    }
}