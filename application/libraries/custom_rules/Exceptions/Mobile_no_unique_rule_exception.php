<?php 

use AJD_validation\Contracts\Abstract_exceptions;

class Mobile_no_unique_rule_exception extends Abstract_exceptions
{
    public static $defaultMessages  = array(
        self::ERR_DEFAULT           => array(
            self::STANDARD          => 'This :field is already taken.',
        ),
        self::ERR_NEGATIVE          => array(
         self::STANDARD             => 'This :field is not yet taken.',
        ),
    );

    public static $localizeFile     = 'mobile_no_unique_rule_err';
}