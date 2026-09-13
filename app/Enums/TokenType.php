<?php

namespace App\Enums;

enum TokenType
{
	case IDENTIFIER;
	case STRING;
	case NULL;
	case BOOL;
	case INT;
	case FLOAT;

	case COMMA;
	case SEMICOLON;
	case LEFT_PAREN;
	case RIGHT_PAREN;
	case LEFT_BRACE;
	case RIGHT_BRACE;

	case PLUS;
	case MINUS;
	case STAR;
	case SLASH;
	case ASSIGN;

	case SIA;
	case COST;
	case SE;
	case SENON;

	case EOF;
}
