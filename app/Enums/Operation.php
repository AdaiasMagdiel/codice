<?php

namespace App\Enums;

enum Operation: int
{
    case LOAD_CONST  = 0x01;
    case STORE_LOCAL = 0x02;
    case LOAD_LOCAL  = 0x03;
    case ADD         = 0x04;
    case CALL_FUNC   = 0x05;
    case PUSH_TRUE   = 0x06;
    case PUSH_FALSE  = 0x07;
    case PUSH_NULL   = 0x08;
}
