<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Type_rule_exception extends Abstract_exceptions
{
	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field must be {type}.',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> ':field must not be {type}.',
        ),
    );

    public static $localizeFile     = 'type_rule_err';
}