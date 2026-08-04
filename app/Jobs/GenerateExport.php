<?php

namespace App\Jobs;

use App\Domain\Reports\ReportService;
use App\Models\ExportJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateExport implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(public readonly int $exportJobId) {}

    public function handle(ReportService $reports): void
    {
        $job = ExportJob::find($this->exportJobId);

        if (! $job) {
            return;
        }

        $job->update(['status' => ExportJob::STATUS_PROCESSING]);

        try {
            $report = $reports->run($job->type, $job->filters ?? []);
            $filename = "exports/{$job->type}-{$job->id}-".now()->format('YmdHis').'.csv';
            $csv = fopen('php://temp', 'w');
            fputcsv($csv, $report['headers']);

            foreach ($report['rows'] as $row) {
                fputcsv($csv, $row);
            }

            rewind($csv);
            $contents = stream_get_contents($csv);
            fclose($csv);

            Storage::disk('evidence')->put($filename, $contents);

            $job->update([
                'status' => ExportJob::STATUS_DONE,
                'file_path' => $filename,
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $job->update([
                'status' => ExportJob::STATUS_FAILED,
                'error' => substr($e->getMessage(), 0, 500),
                'completed_at' => now(),
            ]);
        }
    }
}
