<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\ThemeParkTicket;
use Illuminate\Http\Request;

class EventTicketController extends Controller
{
    /* =========================
     * Public user features
     * ========================= */

    public function index()
    {
        $events = Event::withCount(['tickets as sold_qty' => function($q){
            $q->where('status', '!=', 'cancelled');
        }])->orderBy('schedule')->get();

        return view('events.index', compact('events'));
    }

    public function store(Event $event, Request $request)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $sold = $event->tickets()->where('status','!=','cancelled')->sum('quantity');
        if ($sold + $data['quantity'] > $event->capacity) {
            return back()->withErrors('Capacity exceeded');
        }

        $event->tickets()->create([
            'user_id'  => $request->user()->id,
            'quantity' => $data['quantity'],
            'status'   => 'reserved',
        ]);

        return back()->with('status', 'Ticket reserved!');
    }

    public function confirm(ThemeParkTicket $ticket)
    {
        $this->authorize('update', $ticket);

        $ticket->update(['status' => 'paid']);

        $ticket->booking()->create([
            'event_id'     => $ticket->event_id,
            'booking_time' => now(),
        ]);

        return back()->with('status', 'Booking confirmed');
    }

    /* =========================
     * Event owner features
     * (guard with can:manage-events in routes)
     * ========================= */

    /** Owner: list all events with sold/remaining */
    public function manage()
    {
        // Get events + sold sum (quantity), excluding cancelled
        $events = Event::query()
            ->withSum(['tickets as sold' => function ($q) {
                $q->where('status', '!=', 'cancelled');
            }], 'quantity')
            ->orderByDesc('schedule')
            ->get();

        // Decorate with remaining
        $rows = $events->map(function ($e) {
            $capacity  = (int) ($e->capacity ?? 0);
            $sold      = (int) ($e->sold ?? 0);
            $remaining = max(0, $capacity - $sold);

            return [
                'id'        => $e->id,
                'name'      => $e->name,
                'type'      => $e->type,
                'schedule'  => $e->schedule,
                'location'  => $e->location,
                'capacity'  => $capacity,
                'sold'      => $sold,
                'remaining' => $remaining,
            ];
        });

        return view('events.manage', ['events' => $rows]);
    }

    /** Owner: show create form */
    public function create()
    {
        return view('events.create');
    }

    /** Owner: persist a new event */
    public function storeEvent(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'type'     => ['nullable','string','max:100'],
            'schedule' => ['required','date'],
            'location' => ['nullable','string','max:255'],
            'capacity' => ['required','integer','min:1'],
        ]);

        $event = new Event();
        $event->name     = $data['name'];
        $event->type     = $data['type'] ?? null;
        $event->schedule = $data['schedule'];
        $event->location = $data['location'] ?? null;
        $event->capacity = (int) $data['capacity'];
        $event->save();

        return redirect()->route('events.manage')->with('status', 'Event created.');
    }

    /** Owner: show edit form */
    public function edit($id)
    {
        $event = Event::findOrFail($id);
        return view('events.edit', compact('event'));
    }

    /** Owner: update an existing event */
    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'type'     => ['nullable','string','max:100'],
            'schedule' => ['required','date'],
            'location' => ['nullable','string','max:255'],
            'capacity' => ['required','integer','min:1'],
        ]);

        $event->name     = $data['name'];
        $event->type     = $data['type'] ?? null;
        $event->schedule = $data['schedule'];
        $event->location = $data['location'] ?? null;
        $event->capacity = (int) $data['capacity'];
        $event->save();

        return redirect()->route('events.manage')->with('status', 'Event updated.');
    }

    /** Owner: delete an event */
    public function destroyEvent($id)
    {
        $event = Event::findOrFail($id);

        // If you have FKs with cascade, this is enough.
        // Otherwise, consider soft-deleting or checking for existing tickets.
        $event->delete();

        return redirect()->route('events.manage')->with('status', 'Event deleted.');
    }
}