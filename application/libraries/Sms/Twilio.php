<?php namespace Sms;

require_once APPPATH . 'third_party/twilio/autoload.php';

use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use Exception;

class Twilio implements Sms_api_interface
{

	const ACCOUNT_SID 	= 'ACa6b988560eaf82fe2514dd47d35a2bfa',
		  AUTH_TOKEN 	= 'a914b7fdc5bcc77f052d3c42a027a251',
		  TWILIO_NUMBER = '+19382231637';

	public function sendMessageToUser( $number, $message )
	{
		$response 		= array();

		try 
		{
			$client 		= new Client(self::ACCOUNT_SID, self::AUTH_TOKEN);

			$response 		= $client->messages->create(
				$number, // Text this number
				array(
					'from' => self::TWILIO_NUMBER, // From a valid Twilio number
					'body' => $message
				)
			);
		}
		catch(TwilioException $e)
		{
			$response = $e;
		}

		return $response;
	}

	public function sendMessageToManyLoop( array $number, $message )
	{
		$responses 		= array();

		foreach( $number as $num )
		{
			try 
			{
				$response 	= $this->sendMessageToUser($num, $message);
			}
			catch( TwilioException $e )
			{
				$responses[$num] = $e;
			}

			$responses[$num]	= $response;
		}

		return $responses;
	}
}