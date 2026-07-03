<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class StructuredLogController extends Controller
{
    public function index()
    {
        $logPath = storage_path('logs');
        $files = collect();

        if (File::isDirectory($logPath)) {
            $files = collect(File::files($logPath))
                ->filter(fn ($file) => str_starts_with($file->getFilename(), 'structured-') && $file->getExtension() === 'log')
                ->map(fn ($file) => [
                    'name' => $file->getFilename(),
                    'date' => str_replace(['structured-', '.log'], '', $file->getFilename()),
                    'size' => $file->getSize(),
                    'modified' => $file->getMTime(),
                ])
                ->sortByDesc('date')
                ->values();
        }

        return view('admin.structured-logs.index', compact('files'));
    }

    public function show(string $date)
    {
        $filePath = storage_path("logs/structured-{$date}.log");

        if (! file_exists($filePath)) {
            abort(404, 'Log file not found for date: '.$date);
        }

        $lines = File::lines($filePath)->filter()->values();
        $entries = $lines->map(fn ($line) => json_decode($line, true))->filter();

        if (request('level')) {
            $entries = $entries->where('level_name', request('level'));
        }

        if (request('search')) {
            $search = request('search');
            $entries = $entries->filter(fn ($e) => str_contains($e['message'] ?? '', $search));
        }

        $total = $entries->count();
        $currentPage = request('page', 1);
        $perPage = 50;
        $paginated = $entries->forPage($currentPage, $perPage);

        return view('admin.structured-logs.show', [
            'entries' => $paginated,
            'date' => $date,
            'total' => $total,
            'levels' => ['INFO', 'WARNING', 'ERROR'],
        ]);
    }

    public function download(string $date)
    {
        $filePath = storage_path("logs/structured-{$date}.log");

        if (! file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, "structured-{$date}.log");
    }
}
