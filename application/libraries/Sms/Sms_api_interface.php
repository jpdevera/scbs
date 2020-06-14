<?php namespace Sms;

interface Sms_api_interface 
{
	public function sendMessageToUser( $number, $message );

	public function sendMessageToManyLoop( array $number, $message );
}