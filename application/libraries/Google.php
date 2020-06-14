<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'third_party/google/autoload.php';

class Google
{
	protected $CI;

	/**
     * @var G
     */
    private $G;

    private $Goauth;

    public function __construct()
    {
    	$this->CI =& get_instance();

	 	$this->CI->load->config('google');

        // Load required libraries and helpers
        $this->CI->load->library('session');
        $this->CI->load->helper('url');

        if (!ISSET($this->G))
        {
			$this->G = new Google_Client();

			$this->G->setApplicationName(PROJECT_CODE);
			$this->G->setClientId($this->CI->config->item('google_client_id'));
			$this->G->setClientSecret($this->CI->config->item('google_client_secret'));
			$this->G->setRedirectUri($this->CI->config->item('google_login_redirect_url'));
			$this->G->addScope('https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile');
			// $this->G->setScopes(array(Google_Service_Plus::PLUS_ME));

			if( !ISSET($this->Goauth) )
			{
				$this->Goauth = new Google_Service_Oauth2($this->G);
			}
        }

    }

     /**
     * Check whether the user is logged in.
     * by access token
     *
     * @return mixed|boolean
     */
    public function is_authenticated()
    {
        $access_token = $this->authenticate();

        if(isset($access_token))
        {
            return $access_token;
        }

        return false;
    }


     /**
     * Do Graph request
     *
     * @param       $method
     * @param       $endpoint
     * @param array $params
     * @param null  $access_token
     *
     * @return array
     */
    public function request($method)
    {
        try
        {
            // $response = $this->Goauth->{strtolower($method)}->{strtolower($methodRequest)}($endpoint);
            $response = $this->Goauth->userinfo->{strtolower($method)}();
            return $response;
        }
        catch(Exception $e)
        {
            return $this->logError($e->getCode(), $e->getMessage());
        }
    }


    public function login_url()
    {
        return $this->G->createAuthUrl();
    }

    private function authenticate()
    {
        $access_token = $this->get_access_token();

        if (ISSET($_GET['code'])) 
        {
			$this->G->authenticate($_GET['code']);
        }

        if($access_token)
        {
            $this->set_access_token($access_token);
        }
        // If we did not have a stored access token or if it has expired, try get a new access token
        if(!$access_token)
        {
        	if ($this->G->getAccessToken() && !$this->G->isAccessTokenExpired()) 
        	{
        		 try 
        		 {
        		 	$access_token 	= $this->G->getAccessToken();
        		 	$this->set_access_token($access_token);
        		 }
        		 catch (Google_Exception $e)
        		 {
        		 	$this->logError($e->getCode(), $e->getMessage());
                	return null;
        		 }
        	}
        	else
        	{

        	}
          
        }

        return $access_token;
    }

    /**
     * @param $code
     * @param $message
     *
     * @return array
     */
    private function logError($code, $message)
    {
        log_message('error', '[GOOGLE PHP SDK] code: ' . $code.' | message: '.$message);
        return ['error' => $code, 'message' => $message];
    }

     /**
     * Get stored access token
     *
     * @return mixed
     */
    private function get_access_token()
    {
        return $this->CI->session->userdata('google_access_token');
    }


     /**
     * Store access token
     *
     * @param AccessToken $access_token
     */
    private function set_access_token($access_token)
    {
        $this->CI->session->set_userdata('google_access_token', $access_token);
    }
}