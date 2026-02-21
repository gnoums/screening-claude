<?php

namespace Tests\Feature;

use App\Exceptions\InvalidTokenException;
use App\Models\Patient;
use App\Models\ScreeningRequest;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Feature tests del flujo de tokens de paciente.
 */
class TokenServiceTest extends TestCase
{
    use RefreshDatabase;

    private TokenService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TokenService();
    }

    // ----------------------------------------------------------------
    // Helper
    // ----------------------------------------------------------------

    private function makeRequest(): ScreeningRequest
    {
        $user    = User::factory()->create();
        $patient = Patient::create([
            'user_id'    => $user->id,
            'first_name' => 'Juan',
            'last_name'  => 'Pérez',
        ]);

        return ScreeningRequest::create([
            'user_id'         => $user->id,
            'patient_id'      => $patient->id,
            'status'          => 'sent',
            'recipient_email' => 'juan@example.com',
        ]);
    }

    private function fakeRequest(string $ip = '127.0.0.1'): Request
    {
        $request = Request::create('/test');
        $request->server->set('REMOTE_ADDR', $ip);

        return $request;
    }

    // ----------------------------------------------------------------
    // Tests
    // ----------------------------------------------------------------

    #[Test]
    public function it_generates_a_token_and_stores_only_the_hash(): void
    {
        $screeningRequest = $this->makeRequest();
        ['plain' => $plain, 'token' => $token] = $this->service->generate($screeningRequest);

        $this->assertNotEmpty($plain);
        $this->assertEquals(hash('sha256', $plain), $token->token_hash);
        $this->assertDatabaseHas('screening_tokens', [
            'token_hash' => hash('sha256', $plain),
        ]);
    }

    #[Test]
    public function it_never_stores_the_plain_token(): void
    {
        $screeningRequest = $this->makeRequest();
        ['plain' => $plain] = $this->service->generate($screeningRequest);

        // El token plano nunca debe estar en ninguna columna de la DB
        $this->assertDatabaseMissing('screening_tokens', [
            'token_hash' => $plain, // plano ≠ hash
        ]);
    }

    #[Test]
    public function it_validates_a_valid_token(): void
    {
        $screeningRequest = $this->makeRequest();
        ['plain' => $plain] = $this->service->generate($screeningRequest);

        $httpRequest      = $this->fakeRequest();
        $validatedToken   = $this->service->validate($plain, $httpRequest);

        $this->assertEquals(hash('sha256', $plain), $validatedToken->token_hash);
    }

    #[Test]
    public function it_throws_for_non_existent_token(): void
    {
        $this->expectException(InvalidTokenException::class);
        $this->service->validate('faketoken123', $this->fakeRequest());
    }

    #[Test]
    public function it_throws_for_expired_token(): void
    {
        $screeningRequest = $this->makeRequest();
        ['plain' => $plain, 'token' => $token] = $this->service->generate($screeningRequest);

        // Expirar el token manualmente
        $token->update(['expires_at' => now()->subHour()]);

        $this->expectException(InvalidTokenException::class);
        $this->service->validate($plain, $this->fakeRequest());
    }

    #[Test]
    public function it_throws_for_already_used_token(): void
    {
        $screeningRequest = $this->makeRequest();
        ['plain' => $plain, 'token' => $token] = $this->service->generate($screeningRequest);

        // Simular uso previo
        $token->update(['used_at' => now()->subMinutes(5)]);

        $this->expectException(InvalidTokenException::class);
        $this->service->validate($plain, $this->fakeRequest());
    }

    #[Test]
    public function it_invalidates_token_after_completion(): void
    {
        $screeningRequest = $this->makeRequest();
        ['plain' => $plain, 'token' => $token] = $this->service->generate($screeningRequest);

        $httpRequest = $this->fakeRequest();
        $this->service->validate($plain, $httpRequest); // primer acceso ok

        $token->refresh(); // actualizar estado tras el validate
        $this->service->invalidateAfterCompletion($token, $httpRequest);

        $this->assertTrue($token->fresh()->is_invalidated);
        $this->assertNotNull($token->fresh()->used_at);
    }

    #[Test]
    public function it_invalidates_previous_token_on_regenerate(): void
    {
        $screeningRequest = $this->makeRequest();
        ['plain' => $plain1, 'token' => $token1] = $this->service->generate($screeningRequest);

        // Regenerar token (p.ej. re-send)
        $this->service->generate($screeningRequest->fresh());

        $this->assertTrue($token1->fresh()->is_invalidated);
    }

    #[Test]
    public function it_generates_public_url_without_internal_ids(): void
    {
        $screeningRequest = $this->makeRequest();
        ['plain' => $plain] = $this->service->generate($screeningRequest);

        $url = $this->service->generateUrl($plain);

        // La URL contiene el token plano en la ruta
        $this->assertStringContainsString($plain, $url);

        // La URL tiene el patrón /s/{token} y NO expone IDs en la query string
        $parsedUrl = parse_url($url);
        $this->assertStringStartsWith('/s/', $parsedUrl['path']);
        $this->assertArrayNotHasKey('query', $parsedUrl, 'URL should not expose IDs via query params');
    }

    #[Test]
    public function it_records_ip_and_user_agent_on_access(): void
    {
        $screeningRequest = $this->makeRequest();
        ['plain' => $plain] = $this->service->generate($screeningRequest);

        $request = Request::create('/test', 'GET', [], [], [], [
            'REMOTE_ADDR'     => '192.168.1.100',
            'HTTP_USER_AGENT' => 'TestBrowser/1.0',
        ]);

        $this->service->validate($plain, $request);

        $this->assertDatabaseHas('screening_tokens', [
            'token_hash'      => hash('sha256', $plain),
            'last_ip'         => '192.168.1.100',
            'last_user_agent' => 'TestBrowser/1.0',
        ]);
    }
}
