<?php

enum DischargeType: string
{
    case Authorized = 'Authorized';
    case ReferOut = 'Refer Out';
    case Unauthorized = 'Unauthorized';
    case Absconded = 'Absconded';
    case Deceased = 'Deceased';
}
