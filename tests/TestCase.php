<?php

namespace Tests;

use App\Models\ClinicModel;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * The fixed subdomain used by all test requests.
     * Must match the Route::domain() registration in bootstrap/app.php
     * which is hard-coded to 'dtc.localhost'.
     */
    protected const CLINIC_HOST = 'dtc.localhost';

    /**
     * The clinic instance shared across tests that need it.
     */
    protected ?ClinicModel $clinic = null;

    /**
     * Create (or reuse) a clinic whose subdomain matches the registered route
     * domain ('dtc'), then pre-bind it as 'currentClinic' in the IoC container.
     *
     * The ClinicMiddleware normally resolves this from the subdomain via a DB
     * query; by binding it here we skip that middleware concern entirely.
     */
    protected function bindClinic(?ClinicModel $clinic = null): ClinicModel
    {
        if ($clinic === null) {
            // Use subdomain 'dtc' so it matches Route::domain('dtc.localhost')
            $clinic = ClinicModel::factory()->create(['subdomain' => 'dtc']);
        }

        $this->clinic = $clinic;
        app()->instance('currentClinic', $this->clinic);

        return $this->clinic;
    }

    /**
     * Create a user belonging to the given clinic (creates one if not supplied).
     */
    protected function makeUser(?ClinicModel $clinic = null): User
    {
        $clinic = $clinic ?? $this->clinic ?? $this->bindClinic();
        return User::factory()->create(['clinic_id' => $clinic->id]);
    }

    /**
     * Shorthand: bind a clinic, create a user, log them in, and return the user.
     */
    protected function loginUser(?ClinicModel $clinic = null): User
    {
        $clinic = $this->bindClinic($clinic);
        $user   = $this->makeUser($clinic);
        $this->actingAs($user);
        return $user;
    }

    /**
     * Build a request URL that matches Route::domain('dtc.localhost').
     */
    protected function clinicUrl(string $path): string
    {
        $path = ltrim($path, '/');
        return 'http://' . self::CLINIC_HOST . '/' . $path;
    }
}
