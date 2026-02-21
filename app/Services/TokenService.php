<?php

namespace App\Services;

use App\Exceptions\InvalidTokenException;
use App\Models\AuditLog;
use App\Models\ScreeningRequest;
use App\Models\ScreeningToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Gestiona la generación, validación e invalidación de tokens de paciente.
 *
 * Principios de seguridad aplicados:
 *  - Se guarda SOLO el hash SHA-256; el token plano nunca persiste en DB.
 *  - El token plano se entrega UNA SOLA VEZ (en la URL del email).
 *  - Expiración configurable vía SCREENING_TOKEN_TTL_HOURS.
 *  - Invalidación al completar para prevenir reuse.
 *  - Registro de IP y User-Agent en audit_logs.
 */
class TokenService
{
    /**
     * Genera un token criptográficamente seguro y lo almacena (hasheado).
     *
     * @return array{plain: string, token: ScreeningToken}
     */
    public function generate(ScreeningRequest $screeningRequest): array
    {
        // Invalidar token anterior si existe
        optional($screeningRequest->token)->update(['is_invalidated' => true]);

        $plainToken = Str::random(64); // 64 chars = 384 bits de entropía
        $hash       = hash('sha256', $plainToken);
        $ttlHours   = (int) config('screening.token_ttl_hours', 72);

        $token = ScreeningToken::create([
            'screening_request_id' => $screeningRequest->id,
            'token_hash'           => $hash,
            'expires_at'           => now()->addHours($ttlHours),
            'is_invalidated'       => false,
        ]);

        return [
            'plain' => $plainToken,
            'token' => $token,
        ];
    }

    /**
     * Genera la URL pública del paciente.
     * No expone IDs internos; solo el token plano.
     */
    public function generateUrl(string $plainToken): string
    {
        return route('public.screening.show', ['token' => $plainToken]);
    }

    /**
     * Valida el token y registra el acceso.
     *
     * @throws InvalidTokenException
     */
    public function validate(string $plainToken, Request $request): ScreeningToken
    {
        $token = ScreeningToken::findByPlainToken($plainToken);

        if (! $token) {
            $this->auditInvalidAccess('token.not_found', $request, ['token_prefix' => substr($plainToken, 0, 6)]);
            throw InvalidTokenException::notFound();
        }

        if ($token->is_invalidated) {
            $this->auditInvalidAccess('token.invalidated', $request, [], $token);
            throw InvalidTokenException::invalidated();
        }

        if ($token->isExpired()) {
            $this->auditInvalidAccess('token.expired', $request, [], $token);
            throw InvalidTokenException::expired();
        }

        if ($token->hasBeenUsed()) {
            $this->auditInvalidAccess('token.reuse_attempt', $request, [], $token);
            throw InvalidTokenException::alreadyUsed();
        }

        // Registrar metadatos del acceso (primera vez)
        $token->update([
            'last_ip'         => $request->ip(),
            'last_user_agent' => $request->userAgent(),
        ]);

        AuditLog::record(
            event: 'token.accessed',
            auditable: $token->screeningRequest,
            metadata: ['screening_request_id' => $token->screening_request_id],
            ip: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return $token;
    }

    /**
     * Marca el token como usado (completado), lo invalida para re-uso.
     */
    public function invalidateAfterCompletion(ScreeningToken $token, Request $request): void
    {
        $token->update([
            'used_at'        => now(),
            'is_invalidated' => true,
        ]);

        AuditLog::record(
            event: 'token.completed',
            auditable: $token->screeningRequest,
            metadata: ['screening_request_id' => $token->screening_request_id],
            ip: $request->ip(),
            userAgent: $request->userAgent(),
        );
    }

    /**
     * Invalidación manual (p.ej. la psicóloga cancela el envío).
     */
    public function revoke(ScreeningToken $token): void
    {
        $token->update(['is_invalidated' => true]);

        AuditLog::record(
            event: 'token.revoked',
            auditable: $token->screeningRequest,
            metadata: ['revoked_by' => 'psychologist'],
        );
    }

    // ----------------------------------------------------------------
    // Private helpers
    // ----------------------------------------------------------------

    private function auditInvalidAccess(
        string $event,
        Request $request,
        array $metadata = [],
        ?ScreeningToken $token = null,
    ): void {
        AuditLog::record(
            event: $event,
            auditable: $token?->screeningRequest,
            metadata: $metadata,
            ip: $request->ip(),
            userAgent: $request->userAgent(),
        );
    }
}
