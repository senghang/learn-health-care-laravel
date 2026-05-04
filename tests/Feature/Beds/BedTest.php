<?php

namespace Tests\Feature\Beds;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BedTest extends TestCase
{
    use RefreshDatabase;

    public function test_bed_index_loads_when_authenticated(): void
    {
        $this->loginUser();
        $response = $this->get($this->clinicUrl('/beds'));
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_redirected_from_beds(): void
    {
        $this->bindClinic();
        $response = $this->get($this->clinicUrl('/beds'));
        $response->assertRedirect();
    }
}
