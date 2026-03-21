<?php

namespace App\Common\Enums;
enum AdmissionType: string
{
    case WalkIn = 'Walk-in';
    case Appointment = 'Appointment';
    case Refer = 'Refer';
}
