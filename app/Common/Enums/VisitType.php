<?php

namespace App\Common\Enums;
enum VisitType: string
{
    case IPD = 'IPD-in';
    case OPD = 'OPD';
    case Emergency = 'Emergency';
}
