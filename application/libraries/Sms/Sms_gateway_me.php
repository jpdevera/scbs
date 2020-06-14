<?php namespace Sms;

require_once APPPATH . 'third_party/smsgatewayme/autoload.php';

use SMSGatewayMe\Client\ApiClient;
use SMSGatewayMe\Client\Configuration;
use SMSGatewayMe\Client\Api\MessageApi;
use SMSGatewayMe\Client\Model\SendMessageRequest;

class Sms_gateway_me implements Sms_api_interface
{
	const API_KEY 	= 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhZG1pbiIsImlhdCI6MTU3MjQ4NzUwNywiZXhwIjo0MTAyNDQ0ODAwLCJ1aWQiOjc0ODI2LCJyb2xlcyI6WyJST0xFX1VTRVIiXX0.4e2QWTIfflXui6q7KkJfhtOpUwXXkuAax5tHDPtdSZw',
		  DEVICE_ID = '113917';

	protected $apiClient;
	protected $messageClient;

    public function __construct()
    {
    	$config 	= Configuration::getDefaultConfiguration();
		$config->setApiKey('Authorization', self::API_KEY);

		$this->apiClient 		= new ApiClient($config);
		$this->messageClient 	= new MessageApi($this->apiClient);
    }

    public function sendMessageToUser($number, $message)
    {
    	$sendMessageRequest 	= new SendMessageRequest(array(
		    'phoneNumber' 		=> $number,
		    'message' 			=> $message,
		    'deviceId' 			=> self::DEVICE_ID
		));

		$sendMessages = $this->messageClient->sendMessages(array(
		    $sendMessageRequest
		));

		return $sendMessages;
    }

    public function sendMessageToManyLoop(array $number, $message)
    {
    	$responses 		= array();

    	foreach( $number as $num )
		{
			
			$response 	= $this->sendMessageToUser($num, $message);
			

			$responses[$num]	= $response;
		}

		return $responses;
    }
}