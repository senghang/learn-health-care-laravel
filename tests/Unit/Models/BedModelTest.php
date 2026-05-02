<?php

namespace Tests\Unit\Models;

use App\Models\BedModel;
use Database\Factories\BedFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BedModelTest extends TestCase
{
    use RefreshDatabase;

    // ── isAvailable ──────────────────────────────────────────────────────────

    public function test_available_bed_returns_true(): void
    {
        $bed = BedModel::factory()->available()->create();

        $this->assertTrue($bed->isAvailable());
    }

    public function test_occupied_bed_returns_false(): void
    {
        $bed = BedModel::factory()->occupied()->create();

        $this->assertFalse($bed->isAvailable());
    }

    public function test_cleaning_bed_returns_false(): void
    {
        $bed = BedModel::factory()->cleaning()->create();

        $this->assertFalse($bed->isAvailable());
    }

    // ── assignTo ─────────────────────────────────────────────────────────────

    public function test_assign_to_sets_occupied_and_links_visit_and_patient(): void
    {
        $bed = BedModel::factory()->available()->create();

        $bed->assignTo('V2026042500001', 'PT2026042500001');

        $bed->refresh();
        $this->assertSame('occupied', $bed->status);
        $this->assertSame('V2026042500001', $bed->current_visit_code);
        $this->assertSame('PT2026042500001', $bed->current_patient_code);
    }

    public function test_assign_to_overwrites_previous_assignment(): void
    {
        $bed = BedModel::factory()->available()->create();
        $bed->assignTo('V2026042500001', 'PT2026042500001');

        $bed->assignTo('V2026042599999', 'PT2026042599999');

        $bed->refresh();
        $this->assertSame('occupied', $bed->status);
        $this->assertSame('V2026042599999', $bed->current_visit_code);
    }

    // ── release ──────────────────────────────────────────────────────────────

    public function test_release_sets_cleaning_and_clears_links(): void
    {
        $bed = BedModel::factory()->occupied()->create();

        $bed->release();

        $bed->refresh();
        $this->assertSame('cleaning', $bed->status);
        $this->assertNull($bed->current_visit_code);
        $this->assertNull($bed->current_patient_code);
    }

    public function test_released_bed_is_not_available(): void
    {
        $bed = BedModel::factory()->occupied()->create();

        $bed->release();

        $this->assertFalse($bed->isAvailable());
        $this->assertSame('cleaning', $bed->fresh()->status);
    }

    // ── status constants ──────────────────────────────────────────────────────

    public function test_all_statuses_are_defined(): void
    {
        $expected = ['available', 'occupied', 'cleaning', 'reserved', 'maintenance'];

        $this->assertSame($expected, BedModel::STATUSES);
    }
}
