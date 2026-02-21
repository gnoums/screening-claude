<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ReportExport;
use App\Models\ScreeningRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * Genera y almacena el PDF de resultados de tamizaje.
 */
class PdfExportService
{
    /**
     * Genera el PDF para una solicitud completa y lo persiste en Storage.
     *
     * @throws \DomainException Si la solicitud no está completada
     */
    public function export(ScreeningRequest $request): ReportExport
    {
        if (! $request->isCompleted()) {
            throw new \DomainException(
                'Solo se pueden exportar solicitudes con todas las pruebas completadas.',
            );
        }

        // Cargar relaciones necesarias para la plantilla
        $request->load([
            'patient',
            'user.profile',
            'items.assessment',
            'items.response.answers.question',
            'items.response.answers.option',
        ]);

        $pdf = Pdf::loadView('pdf.screening-report', [
            'request'    => $request,
            'patient'    => $request->patient,
            'psychologist' => $request->user,
            'items'      => $request->items,
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('A4', 'portrait');

        $filename  = "reports/{$request->user_id}/screening_{$request->id}_" . now()->format('Ymd_His') . '.pdf';
        $pdfContent = $pdf->output();

        Storage::disk('local')->put($filename, $pdfContent);

        $export = ReportExport::create([
            'user_id'             => $request->user_id,
            'screening_request_id' => $request->id,
            'file_path'           => $filename,
            'format'              => 'pdf',
            'file_size_bytes'     => strlen($pdfContent),
            'generated_at'        => now(),
        ]);

        AuditLog::record(
            event: 'pdf.generated',
            auditable: $request,
            metadata: [
                'export_id'    => $export->id,
                'file_size'    => $export->file_size_bytes,
            ],
            userId: $request->user_id,
        );

        return $export;
    }

    /**
     * Devuelve la respuesta HTTP con el PDF para descarga inline.
     */
    public function download(ScreeningRequest $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $export = $this->export($request);

        return Storage::disk('local')->download(
            $export->file_path,
            "tamizaje_paciente_{$request->patient->last_name}_{$request->id}.pdf",
            ['Content-Type' => 'application/pdf'],
        );
    }
}
