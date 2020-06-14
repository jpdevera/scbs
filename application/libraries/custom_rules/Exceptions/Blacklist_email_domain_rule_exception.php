<?php 

use AJD_validation\Contracts\Abstract_exceptions;

class Blacklist_email_domain_rule_exception extends Abstract_exceptions
{
    public static $defaultMessages  = array(
        self::ERR_DEFAULT           => array(
            self::STANDARD          => ':field with {blacklistedEmail} is not allowed.',
        ),
        self::ERR_NEGATIVE          => array(
         self::STANDARD             => ':field with {blacklistedEmail} is  allowed.',
        ),
    );

    public static $localizeFile     = 'allowed_email_domain_rule_err';
}