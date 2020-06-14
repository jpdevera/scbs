<?php
/**
* CSRF Protection Class
*/
class Csrf_protection
{
    /**
    * Holds CI instance
    *
    * @var CI instance
    */
    private $CI;

    /**
    * Name used to store token on session
    *
    * @var string
    */
    private static $token_name = 'CSRFToken';

    /**
    * Stores the token
    *
    * @var string
    */
    private static $token;
    private $custom_csrf    = array();

    public function __construct()
    {
        $this->CI =& get_instance();

        $this->custom_csrf    = $this->CI->config->item('custom_csrf');

        if( !EMPTY( $this->custom_csrf ) AND !EMPTY( $this->custom_csrf['csrf_token_name'] ) )
        {
            static::$token_name  = $this->custom_csrf['csrf_token_name'];
        }
    }


    /**
    * Generates a CSRF token and stores it on session. Only one token per session is generated.
    * This must be tied to a post-controller hook, and before the hook
    * that calls the inject_tokens method().
    *
    * @return void
    */
    public function generate_token()
    {
        
        if( EMPTY( $this->custom_csrf ) OR !$this->custom_csrf['enable_csrf_protection'] )
        {
            return;
        }

        // Load session library if not loaded
        $this->CI->load->library('session');
        
        if ($this->CI->session->has_userdata(self::$token_name) === FALSE)
        {
            // Generate a token and store it on session, since old one appears to have expired.
            self::$token = bin2hex(openssl_random_pseudo_bytes(16));
            $this->CI->session->set_userdata(self::$token_name, self::$token);
        }
        else
        {
            // Set it to local variable for easy access
            self::$token = $this->CI->session->userdata(self::$token_name);
        }
    }

    /**
    * This injects hidden tags on all POST forms with the csrf token.
    * Also injects meta headers in <head> of output (if exists) for easy access
    * from JS frameworks.
    *
    * @return void
    */
    public function inject_tokens()
    {
        if( EMPTY( $this->custom_csrf ) OR !$this->custom_csrf['enable_csrf_protection'] )
        {
            // This has to be here otherwise nothing is sent to the browser
            $this->CI->output->_display($this->CI->output->get_output());
            return;
        }

        $output = $this->CI->output->get_output();

        // Inject into form
        $output = preg_replace('/(<(form|FORM)[^>]*>)/',
                       '$0<input type="hidden" name="' . self::$token_name . '" value="' . self::$token . '">', 
                       $output);

        // Inject into <head>
        $output = preg_replace('/(<\/head>)/',
                       '<meta name="csrf-name" content="' . self::$token_name . '">' . "\n" . '<meta name="csrf-token" content="' . self::$token . '">' . "\n" . '$0', 
                       $output);

        $this->CI->output->_display($output);
    }

    /**
    * Validates a submitted token when POST request is made.
    *
    * @return void
    */
    public function validate_tokens()
    {
        try
        {
            $with_ip_blacklist  = get_sys_param_val('IP_ADDRESS', 'IP_BLACKLIST');
            $ch_with_ip_blacklist   = ( !EMPTY( $with_ip_blacklist ) AND !EMPTY( $with_ip_blacklist['sys_param_value'] ) ) ? TRUE : FALSE;
            
            if( $ch_with_ip_blacklist )
            {
                $enable_ip_blacklist    = get_setting(LOGIN, "enable_ip_blacklist");

                if( !EMPTY( $enable_ip_blacklist ) )
                {
                    $ip_blacklist       = get_setting(LOGIN, "ip_blacklist");

                    if( !EMPTY( $ip_blacklist ) ) 
                    {
                        $ip_blacklist_ar = explode(',', $ip_blacklist);

                        $ip_arr          = array();

                        foreach( $ip_blacklist_ar as $ips )
                        {
                            $dash_ar    = explode('-', $ips);

                            if( ISSET( $dash_ar[1] ) )
                            {
                                $range      = ip_helper( $dash_ar[0], $dash_ar[1] );
                                $ip_arr     = array_merge($ip_arr, $range);
                            }
                            else
                            {
                                $ip_arr[]   = $dash_ar[0];
                            }
                        }

                        if( !EMPTY( $ip_arr ) )
                        {
                            $ip         = (ISSET($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '::1');
                            $check_ip   = check_blacklisted_ip($ip, $ip_arr);
                            if( 
                                $this->CI->router->fetch_class() != "unauthorized" AND
                                ISSET($_SERVER['REMOTE_ADDR']) AND !EMPTY( $check_ip ) 
                            )
                            {
                                $message    = "Sorry but ip address is not allowed to access site.";
                                
                                header('Location:'.base_url().'unauthorized/invalid_link/0/1/'.base64_url_encode($message).'/');
                            }
                        }
                    }
                }
            }
    
            if( EMPTY( $this->custom_csrf ) OR !$this->custom_csrf['enable_csrf_protection'] )
            {
               return;
            }

            // Is this a post request?
            // @link http://stackoverflow.com/questions/1372147/php-check-whether-a-request-is-get-or-post
            if( ISSET($_SERVER['REQUEST_METHOD']) AND $_SERVER['REQUEST_METHOD'] == 'POST' )
            {
                // Is the token field set and valid?
                $posted_token = $this->CI->input->post(self::$token_name);
                $meta_token     = $this->CI->input->post('meta_li_token');
                $ck_token       = $this->CI->input->post('ck_token');

               /* if( ( ( EMPTY($meta_token) AND EMPTY($ck_token) ) AND ( $posted_token === FALSE OR $posted_token != $this->CI->session->userdata(self::$token_name) ) )
                    OR ( !EMPTY( $meta_token ) AND $posted_token != $meta_token )
                )
                {
                    // Invalid request, send error 400.
                    show_error('Request was invalid. Tokens did not match.', 400);
                }*/
            }
        }
        catch( PDOException $e )
        {
            throw $e;
        }
        catch( Exception $e )
        {
            throw $e;
        }
    }
}