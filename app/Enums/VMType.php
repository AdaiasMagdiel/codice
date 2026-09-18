<?php

namespace App\Enums;

enum VMType: int
{
    case NULL  = 0x01;
    case INT   = 0x02;
    case FLOAT = 0x03;
    case STR   = 0x04;
    case BOOL  = 0x05;
}
