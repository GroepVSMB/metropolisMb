<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use App\Models\QualityMetric;

class SimulationExportController extends Controller
{

    public function export(Request $request)
    {
        try {
            // Get all metrics
            $metrics = QualityMetric::all(); // Collection

            // Map IDs to names
            $scoresByName = [];
            foreach ($request['scores'] as $id => $value) {
                $metric = $metrics->firstWhere('id', $id);
                $metricName = $metric->name ?? "Metric $id"; // fallback if missing
                $scoresByName[$metricName] = $value; // keep the actual score
            }
        } catch (\Exception $e) {
            dd($e->getMessage()); // dump the error
        }


        $pdf = Pdf::loadView('pdf.simulation-report', [
            'gridState' => $request['grid_state'],
            'scores' => $scoresByName,
            'events' => $request['events'] ?? [],
            'mapImage' => $request['map_image'],
            'user' => Auth::user(),
            'exportedAt' => now(),
        ])->setPaper('a4', 'portrait');

        // Save PDF to a temporary file
        $fileName = 'simulation-report-' . now()->format('Y-m-d_H-i') . '.pdf';
        $filePath = storage_path('app/public/' . $fileName);
        $pdf->save($filePath);

        // Return the PDF as a download response
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
