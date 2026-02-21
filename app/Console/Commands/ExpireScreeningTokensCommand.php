<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\ScreeningRequest;
use App\Models\ScreeningToken;
use Illuminate\Console\Command;

/**
 * Invalida tokens expirados y actualiza el estado de las solicitudes.
 * Programado vía cron en cPanel: cada hora.
 *
 * php artisan screening:expire-tokens
 */
class ExpireScreeningTokensCommand extends Command
{
    protected $signature   = 'screening:expire-tokens';
    protected $description = 'Invalida tokens de tamizaje vencidos y marca las solicitudes como expiradas';

    public function handle(): int
    {
        $expired = ScreeningToken::query()
            ->where('expires_at', '<', now())
            ->where('is_invalidated', false)
            ->with('screeningRequest')
            ->get();

        $count = 0;
        foreach ($expired as $token) {
            $token->update(['is_invalidated' => true]);

            $request = $token->screeningRequest;

            if ($request && ! in_array($request->status, ['completed', 'cancelled'])) {
                $request->update(['status' => 'expired']);

                AuditLog::record(
                    event: 'token.expired_by_cron',
                    auditable: $request,
                    metadata: ['token_id' => $token->id],
                );
            }

            $count++;
        }

        $this->info("Tokens expirados invalidados: {$count}");

        return self::SUCCESS;
    }
}
