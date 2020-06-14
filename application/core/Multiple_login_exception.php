<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This class is a custom exception for multiple login
 * 
 * @author asiagate
 */
class Multiple_login_exception extends Exception
{
	public function __construct($message, $code = 0, Exception $previous = null) 
	{
        parent::__construct($message, $code, $previous);
    }
}