<?php

namespace App\Common\Utils;

use App\Common\Constants\DateFormats;
use Illuminate\Support\Facades\DB;

/**
 * CodeGenerator — single source of truth for all record code generation.
 */
final class CodeGenerator
{
    /**
     * @deprecated Use ClinicCodeService::visit($clinicId) instead.
     * This method was broken: lockForUpdate() + count() fails on PostgreSQL.
     * Kept for backward compatibility — delegates to ClinicCodeService.
     */
    public static function visit(): string
    {
        $clinicId = app()->has('currentClinic') ? app('currentClinic')->id : 1;
        return \App\Services\ClinicCodeService::visit($clinicId);
    }

    public static function visitPreview(): string
    {
        $clinicId = app()->has('currentClinic') ? app('currentClinic')->id : 1;
        return \App\Services\ClinicCodeService::visitPreview($clinicId);
    }

    public static function patient(): string
    {
        $clinicId = app()->has('currentClinic') ? app('currentClinic')->id : 1;
        return \App\Services\ClinicCodeService::patient($clinicId);
    }

    public static function patientPreview(): string
    {
        $clinicId = app()->has('currentClinic') ? app('currentClinic')->id : 1;
        return \App\Services\ClinicCodeService::patientPreview($clinicId);
    }

    public static function prescription(string $visitCode): string { return 'RX-' . $visitCode; }
    public static function invoice(string $visitCode): string       { return 'INV-' . $visitCode; }
    public static function triage(string $visitCode): string        { return 'TR-' . $visitCode; }

    public static function encounter(string $visitCode, string $visitType): string
    {
        return (strtoupper($visitType) === 'IPD' ? 'IPD' : 'OPD') . '-' . $visitCode;
    }

    public static function vitalSign(string $visitCode): string     { return 'VS-'  . $visitCode . '-' . now()->timestamp; }
    public static function referral(string $visitCode): string      { return 'REF-' . $visitCode . '-' . now()->timestamp; }

    public static function labRequest(string $visitCode): string
    {
        return 'EL-' . $visitCode . '-' . now()->format(DateFormats::CODE_DATE . 'Hisv') . '-' . substr(uniqid(), -6);
    }

    public static function imageryRequest(string $visitCode): string
    {
        return 'IM-' . $visitCode . '-' . now()->format(DateFormats::CODE_DATE . 'Hisv') . '-' . substr(uniqid(), -6);
    }

    public static function ward(int $clinicId): string
    {
        $count = \App\Models\WardModel::where('clinic_id', $clinicId)->count();
        return 'WD-' . $clinicId . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }

    public static function room(int $wardId): string
    {
        $count = \App\Models\RoomModel::where('ward_id', $wardId)->count();
        return 'RM-' . $wardId . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }

    public static function bed(int $wardId): string
    {
        $count = \App\Models\BedModel::where('ward_id', $wardId)->count();
        return 'BD-' . $wardId . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }

    public static function printTemplate(int $clinicId, string $type): string
    {
        $count = \App\Models\PrintTemplateModel::where('clinic_id', $clinicId)->where('type', $type)->count();
        return 'TPL-' . $clinicId . '-' . strtoupper(substr($type, 0, 3)) . '-' . str_pad($count + 1, 2, '0', STR_PAD_LEFT);
    }

    private function __construct() {}
}
