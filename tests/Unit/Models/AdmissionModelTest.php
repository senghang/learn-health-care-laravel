<?php

namespace Tests\Unit\Models;

use App\Models\AdmissionModel;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionModelTest extends TestCase
{
    use RefreshDatabase;

    // ── Status checks ─────────────────────────────────────────────────────────

    public function test_is_admitted_returns_true_for_admitted_status(): void
    {
        $adm = AdmissionModel::factory()->admitted()->create();

        $this->assertTrue($adm->isAdmitted());
        $this->assertFalse($adm->isDischarged());
    }

    public function test_is_discharged_returns_true_for_discharged_status(): void
    {
        $adm = AdmissionModel::factory()->discharged()->create();

        $this->assertTrue($adm->isDischarged());
        $this->assertFalse($adm->isAdmitted());
    }

    public function test_is_active_is_true_when_admitted(): void
    {
        $adm = AdmissionModel::factory()->admitted()->create();

        $this->assertTrue($adm->isActive());
    }

    public function test_is_active_is_false_when_discharged(): void
    {
        $adm = AdmissionModel::factory()->discharged()->create();

        $this->assertFalse($adm->isActive());
    }

    // ── Length of stay ────────────────────────────────────────────────────────

    public function test_length_of_stay_returns_days_since_admission(): void
    {
        $adm = AdmissionModel::factory()->admitted()->create([
            'admitted_at' => now()->subDays(3),
        ]);

        $this->assertSame(3, $adm->length_of_stay);
    }

    public function test_length_of_stay_is_zero_for_same_day_admission(): void
    {
        $adm = AdmissionModel::factory()->admitted()->create([
            'admitted_at' => now(),
        ]);

        $this->assertSame(0, $adm->length_of_stay);
    }

    public function test_length_of_stay_uses_discharged_at_when_set(): void
    {
        $adm = AdmissionModel::factory()->discharged()->create([
            'admitted_at'   => Carbon::parse('2026-04-01 08:00'),
            'discharged_at' => Carbon::parse('2026-04-06 14:00'),
        ]);

        $this->assertSame(5, $adm->length_of_stay);
    }

    public function test_length_of_stay_is_null_when_no_admitted_at(): void
    {
        $adm = AdmissionModel::factory()->create(['admitted_at' => null]);

        $this->assertNull($adm->length_of_stay);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function test_active_scope_returns_only_admitted_records(): void
    {
        AdmissionModel::factory()->admitted()->create();
        AdmissionModel::factory()->discharged()->create();
        AdmissionModel::factory()->create(['status' => 'cancelled']);

        $active = AdmissionModel::active()->get();

        $this->assertCount(1, $active);
        $this->assertSame('admitted', $active->first()->status);
    }

    public function test_discharged_scope_returns_only_discharged_records(): void
    {
        AdmissionModel::factory()->admitted()->create();
        AdmissionModel::factory()->discharged()->count(2)->create();

        $discharged = AdmissionModel::discharged()->get();

        $this->assertCount(2, $discharged);
        $discharged->each(fn($a) => $this->assertSame('discharged', $a->status));
    }

    // ── Status constants ──────────────────────────────────────────────────────

    public function test_all_status_constants_are_defined(): void
    {
        $this->assertSame('admitted',    AdmissionModel::STATUS_ADMITTED);
        $this->assertSame('discharged',  AdmissionModel::STATUS_DISCHARGED);
        $this->assertSame('transferred', AdmissionModel::STATUS_TRANSFERRED);
        $this->assertSame('deceased',    AdmissionModel::STATUS_DECEASED);
        $this->assertSame('cancelled',   AdmissionModel::STATUS_CANCELLED);
    }
}
