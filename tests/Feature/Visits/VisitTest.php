<?php

namespace Tests\Feature\Visits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitTest extends TestCase
{
    use RefreshDatabase;

    public function test_visit_index_loads_when_authenticated(): void
    {
        $this->loginUser();
        $response = $this->get($this->clinicUrl('/visits'));
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_redirected_from_visits(): void
    {
        $this->bindClinic();
        $response = $this->get($this->clinicUrl('/visits'));
        $response->assertRedirect();
    }
}
