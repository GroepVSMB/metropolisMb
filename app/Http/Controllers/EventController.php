<?php

namespace App\Http\Controllers;

use App\Models\SimulationEvent;
use App\Models\QualityMetric; // Changed from Category
use App\Models\EventImpact;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        // Eager load the new relationship
        $events = SimulationEvent::with('impacts.qualityMetric')->get();
        return view('events.index', compact('events'));
    }

    public function create()
    {
        // Fetch Metrics (e.g. Noise, Air Quality) for the form
        $metrics = QualityMetric::all();
        return view('events.create', compact('metrics'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:one_off,recurring',
            'duration_minutes' => 'required|integer|min:1',
            'impacts' => 'nullable|array',
        ]);

        $event = SimulationEvent::create([
            'name' => $request->name,
            'type' => $request->type,
            'duration_minutes' => $request->duration_minutes,
            'recurrence_interval_minutes' => $request->recurrence_interval_minutes ?? null,
        ]);

        if ($request->impacts) {
            foreach ($request->impacts as $metricId => $val) {
                if (!is_null($val) && $val != 0) {
                    EventImpact::create([
                        'simulation_event_id' => $event->id,
                        'quality_metric_id' => $metricId, // Saving Metric ID
                        'impact' => $val,
                    ]);
                }
            }
        }

        return redirect()->route('events.index')->with('success', 'Event aangemaakt!');
    }

    public function edit($id)
    {
        $event = SimulationEvent::with('impacts')->findOrFail($id);
        $metrics = QualityMetric::all();

        // Map impacts so the view can fill the inputs: [metric_id => impact_value]
        $currentImpacts = $event->impacts->pluck('impact', 'quality_metric_id')->toArray();

        return view('events.edit', compact('event', 'metrics', 'currentImpacts'));
    }

    public function update(Request $request, $id)
    {
        $event = SimulationEvent::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:one_off,recurring',
            'duration_minutes' => 'required|integer|min:1',
            'impacts' => 'nullable|array',
        ]);

        $event->update([
            'name' => $request->name,
            'type' => $request->type,
            'duration_minutes' => $request->duration_minutes,
            'recurrence_interval_minutes' => $request->recurrence_interval_minutes ?? null,
        ]);

        // Sync Impacts
        $event->impacts()->delete();

        if ($request->impacts) {
            foreach ($request->impacts as $metricId => $val) {
                if (!is_null($val) && $val != 0) {
                    EventImpact::create([
                        'simulation_event_id' => $event->id,
                        'quality_metric_id' => $metricId,
                        'impact' => $val,
                    ]);
                }
            }
        }

        return redirect()->route('events.index')->with('success', 'Event bijgewerkt!');
    }

    public function destroy($id)
    {
        $event = SimulationEvent::findOrFail($id);
        $event->delete();
        return back()->with('success', 'Event verwijderd.');
    }
}
