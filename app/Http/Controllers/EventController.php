<?php

namespace App\Http\Controllers;

use App\Models\SimulationEvent;
use App\Models\QualityMetric;
use App\Models\EventImpact;
use App\Models\Category;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        // Eager load 'categories' (plural)
        $events = SimulationEvent::with(['impacts.qualityMetric', 'categories'])->get();
        return view('events.index', compact('events'));
    }

    public function create()
    {
        $metrics = QualityMetric::all();
        $categories = Category::all();
        return view('events.create', compact('metrics', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'categories' => 'nullable|array',         // Expect an array
            'categories.*' => 'exists:categories,id', // Verify IDs
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

        // Save Multiple Categories
        if ($request->categories) {
            $event->categories()->sync($request->categories);
        }

        $this->saveImpacts($event, $request->impacts);

        return redirect()->route('events.index')->with('success', 'Event aangemaakt!');
    }

    public function edit($id)
    {
        $event = SimulationEvent::with(['impacts', 'categories'])->findOrFail($id);
        $metrics = QualityMetric::all();
        $categories = Category::all();
        $currentImpacts = $event->impacts->pluck('impact', 'quality_metric_id')->toArray();

        return view('events.edit', compact('event', 'metrics', 'categories', 'currentImpacts'));
    }

    public function update(Request $request, $id)
    {
        $event = SimulationEvent::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
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

        // Sync Multiple Categories
        $event->categories()->sync($request->categories ?? []);

        // Sync Impacts
        $event->impacts()->delete();
        $this->saveImpacts($event, $request->impacts);

        return redirect()->route('events.index')->with('success', 'Event bijgewerkt!');
    }

    private function saveImpacts($event, $impacts) {
        if ($impacts) {
            foreach ($impacts as $metricId => $val) {
                if (!is_null($val) && $val != 0) {
                    EventImpact::create([
                        'simulation_event_id' => $event->id,
                        'quality_metric_id' => $metricId,
                        'impact' => $val,
                    ]);
                }
            }
        }
    }

    // destroy...
}
