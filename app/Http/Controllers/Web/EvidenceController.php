<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TaskEvidence;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EvidenceController extends Controller
{
    public function view(TaskEvidence $evidence): StreamedResponse
    {
        abort_unless(request()->user()->hasPermission('4.1'), 403);

        $path = Storage::disk('evidence')->path($evidence->file_path);

        abort_unless(is_file($path), 404);

        return response()->streamDownload(
            fn () => readfile($path),
            $evidence->original_filename ?: basename($evidence->file_path),
            ['Content-Type' => $evidence->mime_type ?: 'application/octet-stream', 'Content-Disposition' => 'inline'],
        );
    }
}
