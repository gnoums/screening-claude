<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pruebas de control de acceso al panel admin.
 */
class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makePsychologist(): User
    {
        return User::factory()->create(['role' => 'psychologist']);
    }

    // ----------------------------------------------------------------

    #[Test]
    public function admin_can_access_admin_dashboard(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewIs('admin.dashboard');
    }

    #[Test]
    public function psychologist_cannot_access_admin_dashboard(): void
    {
        $psychologist = $this->makePsychologist();

        $this->actingAs($psychologist)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    #[Test]
    public function guest_is_redirected_from_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function admin_can_list_users(): void
    {
        $admin = $this->makeAdmin();
        $this->makePsychologist();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertViewIs('admin.users.index');
    }

    #[Test]
    public function admin_can_view_user_detail(): void
    {
        $admin        = $this->makeAdmin();
        $psychologist = $this->makePsychologist();

        $this->actingAs($admin)
            ->get(route('admin.users.show', $psychologist))
            ->assertOk()
            ->assertViewIs('admin.users.show');
    }

    #[Test]
    public function admin_can_adjust_user_credits(): void
    {
        $admin        = $this->makeAdmin();
        $psychologist = $this->makePsychologist();

        $this->actingAs($admin)
            ->post(route('admin.users.credits', $psychologist), [
                'amount'      => 10,
                'description' => 'Bonificación de prueba',
            ])
            ->assertRedirect();

        $this->assertEquals(10, $psychologist->fresh()->creditBalance());
    }

    #[Test]
    public function admin_can_deduct_credits_with_negative_amount(): void
    {
        $admin        = $this->makeAdmin();
        $psychologist = $this->makePsychologist();

        // Primero acreditar
        $this->actingAs($admin)
            ->post(route('admin.users.credits', $psychologist), [
                'amount'      => 20,
                'description' => 'Recarga inicial',
            ]);

        // Luego descontar
        $this->actingAs($admin)
            ->post(route('admin.users.credits', $psychologist), [
                'amount'      => -5,
                'description' => 'Corrección',
            ]);

        $this->assertEquals(15, $psychologist->fresh()->creditBalance());
    }

    #[Test]
    public function admin_can_change_user_role(): void
    {
        $admin        = $this->makeAdmin();
        $psychologist = $this->makePsychologist();

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $psychologist), ['role' => 'admin'])
            ->assertRedirect();

        $this->assertEquals('admin', $psychologist->fresh()->role);
    }

    #[Test]
    public function admin_cannot_change_own_role(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $admin), ['role' => 'psychologist'])
            ->assertStatus(422);
    }

    #[Test]
    public function admin_can_view_audit_logs(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index'))
            ->assertOk()
            ->assertViewIs('admin.audit-logs.index');
    }

    #[Test]
    public function admin_can_manage_assessments(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.assessments.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.assessments.create'))
            ->assertOk();
    }

    #[Test]
    public function admin_can_create_assessment(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.assessments.store'), [
                'slug'              => 'test-scale',
                'name'              => 'Test Scale',
                'estimated_minutes' => 10,
                'credits_cost'      => 2,
                'is_active'         => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('assessments', ['slug' => 'test-scale']);
    }

    #[Test]
    public function psychologist_cannot_access_admin_assessments(): void
    {
        $psychologist = $this->makePsychologist();

        $this->actingAs($psychologist)
            ->get(route('admin.assessments.index'))
            ->assertForbidden();
    }
}
