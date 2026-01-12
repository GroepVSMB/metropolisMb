<?php

namespace App\Http\Controllers;

use App\Models\SimulationEvent;
use App\Models\Category;
use App\Models\EventImpact;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = SimulationEvent::with('impacts.category')->get();
        return view('events.index', compact('events'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('events.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:one_off,recurring',
            'duration_minutes' => 'required|integer|min:1',
            // Valideer dat impacts een array is (key = category_id, value = adjustment)
            'impacts' => 'nullable|array',
            'impacts.*' => 'nullable|integer', 
        ]);

        // 1. Maak het Event
        $event = SimulationEvent::create([
            'name' => $request->name,
            'type' => $request->type,
            'duration_minutes' => $request->duration_minutes,
            'recurrence_interval_minutes' => $request->recurrence_interval_minutes ?? null,
        ]);

        // 2. Sla de Impacts op
        if ($request->impacts) {
            foreach ($request->impacts as $categoryId => $adjustment) {
                // Alleen opslaan als er daadwerkelijk een waarde is ingevuld (niet 0 of leeg mag ook, afhankelijk van wens. Hier: alles wat niet null is)
                if (!is_null($adjustment) && $adjustment != 0) {
                    EventImpact::create([
                        'simulation_event_id' => $event->id,
                        'category_id' => $categoryId,
                        'livability_adjustment' => $adjustment,
                    ]);
                }
            }
        }

        return redirect()->route('events.index')->with('success', 'Event aangemaakt!');
    }

    public function edit($id)
    {
        $event = SimulationEvent::with('impacts')->findOrFail($id);
        $categories = Category::all();
        
        // Maak een handige lookup array voor de view: [category_id => adjustment]
        $currentImpacts = $event->impacts->pluck('livability_adjustment', 'category_id')->toArray();

        return view('events.edit', compact('event', 'categories', 'currentImpacts'));
    }

    public function update(Request $request, $id)
    {
        $event = SimulationEvent::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:one_off,recurring',
            'duration_minutes' => 'required|integer|min:1',
            'impacts' => 'nullable|array',
            'impacts.*' => 'nullable|integer',
        ]);

        // 1. Update Event Details
        $event->update([
            'name' => $request->name,
            'type' => $request->type,
            'duration_minutes' => $request->duration_minutes,
            'recurrence_interval_minutes' => $request->recurrence_interval_minutes ?? null,
        ]);

        // 2. Sync Impacts (Verwijder oude, maak nieuwe)
        // Dit is de simpelste manier: alles wissen en opnieuw opslaan
        $event->impacts()->delete();

        if ($request->impacts) {
            foreach ($request->impacts as $categoryId => $adjustment) {
                if (!is_null($adjustment) && $adjustment != 0) {
                    EventImpact::create([
                        'simulation_event_id' => $event->id,
                        'category_id' => $categoryId,
                        'livability_adjustment' => $adjustment,
                    ]);
                }
            }
        }

        return redirect()->route('events.index')->with('success', 'Event bijgewerkt!');
    }

    public function destroy($id)
    {
        $event = SimulationEvent::findOrFail($id);
        $event->delete(); // Impacts worden automatisch verwijderd door 'cascade' in migratie
        return back()->with('success', 'Event verwijderd.');
    }
}