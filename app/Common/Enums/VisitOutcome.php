<?php

namespace App\Common\Enums;
enum VisitOutcome: string
{
    case Recovered = 'Recovered';
    case Improved = 'Improved';
    case Unchanged = 'Unchanged';
    case Deceased = 'Deceased';
}
