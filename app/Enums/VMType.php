<?php

namespace App\Enums;

enum VMType: int
{
    case NULL = 0x01;
    case INT  = 0x02;
    case STR  = 0x03;
}
