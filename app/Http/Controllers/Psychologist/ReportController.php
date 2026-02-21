<?php

namespace App\Http\Controllers\Psychologist;

use App\Http\Controllers\Controller;
use App\Models\ScreeningRequest;
use App\Services\PdfExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Genera y descarga el PDF de resultados.
 * Solo accesible por la psicóloga propietaria de la solicitud.
 */
class ReportController extends Controller
{
    public function __construct(
        private readonly PdfExportService $pdfService,
    ) {}

    public function download(Request $request, ScreeningRequest $screening): StreamedResponse
    {
        // Solo la dueña puede descargar
        abort_unless($screening->user_id === $request->user()->id, 403);

        try {
            return $this->pdfService->download($screening);
        } catch (\DomainException $e) {
            abort(422, $e->getMessage());
        }
    }
}
