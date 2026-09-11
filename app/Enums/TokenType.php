<?php

namespace App\Enums;

enum TokenType
{
	case IDENTIFIER;
	case STRING;
	case NULL;
	case BOOL;

	case SEMICOLON;
	case LEFT_PAREN;
	case RIGHT_PAREN;
	case COMMA;

	case EOF;
}
