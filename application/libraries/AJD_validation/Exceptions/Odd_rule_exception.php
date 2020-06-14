<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Odd_rule_exception extends Abstract_exceptions
{
	public static $defaultMessages 	= array(
		self::ERR_DEFAULT 			=> array(
			self::STANDARD 			=> ':field must be an odd number.'
		),
		self::ERR_NEGATIVE 			=> array(
			self::STANDARD 			=> ':field must not be an odd number.'
		)
	);

	public static $localizeFile     = 'odd_rule_err';
}