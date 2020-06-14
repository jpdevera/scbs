<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Session extends CI_Session 
{

/**
* 
* Update an existing session only if the request is not ajax to avoid a sudden session timeout.
*
*/
    public function sess_update()
    {
		$CI =& get_instance();
		
       	// skip the session update if this is an AJAX call!
       	if ( ! $CI->input->is_ajax_request())
       		parent::sess_update();
    } 

}  