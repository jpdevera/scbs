<?php

require 'autoload.php';

class Sms_api
{
	const SERIAL 	= 'Php_serial',
		  TWILIO 	= 'Twilio',
		  SMSGME 	= 'Sms_gateway_me';

	protected $apiTypes = array();
	protected $apiObj 	= NULL;
	protected $apiType 	= NULL;

	public function __construct()
	{
		$this->apiTypes = array(
			'serial' 	=> self::SERIAL,
			'twilio'	=> self::TWILIO,
			'smsgatewayme' => self::SMSGME
		);

		$sms_api 		= get_setting(SMS_API, 'sms_api');

		$real_sms_api 	= DEFAULT_SMS_API;

		if( !EMPTY( $sms_api ) )
		{
			$real_sms_api = $sms_api;
		}

		$this->setSmsApi($real_sms_api);
	}

	public function setSmsApi($apiType)
	{
		$this->apiType = $apiType;

		if( ISSET( $this->apiTypes[$apiType] ) )
		{
			$apiClass 	= 'Sms\\'.$this->apiTypes[$apiType];
			
			if( class_exists($apiClass) ) 
			{
				$this->apiObj 	= new $apiClass;

				return true;
			}
			else 
			{
				return false;
			}
		}
	}

	public function getSmsApi()
	{
		return $this->apiObj;
	}

	protected function processNumber($number)
	{
		if( $this->apiType != 'serial' )
		{
			if( strlen($number) == 11 )
			{
            	$clean_no   = substr($number, 1);
            }
            else
            {
            	$clean_no 	= $number;
            }

            $real_number 	= "+63".$clean_no;

            return $real_number;
		}
		else
		{
			if( strlen($number) == 10 )
			{
				return '0'.$number;
			}
			else
			{
				return $number;
			}
		}
	}

	public function sendMessageToUser($number, $message)
	{
		if( !EMPTY( $this->apiObj ) )
		{
			$real_number 	= $this->processNumber($number);
			
			$response = $this->apiObj->sendMessageToUser($real_number, $message);

			return $response;
		}

		return false;
	}

	public function sendMessageToManyLoop(array $numbers, $message)
	{
		if( !EMPTY( $this->apiObj ) )
		{
			$response = $this->apiObj->sendMessageToManyLoop($numbers, $message);

			return $response;
		}

		return false;
	}
}