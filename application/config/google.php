<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
|  GOOGLE API Configuration
| -------------------------------------------------------------------
|
| To get an facebook app details you have to create a Facebook app
| at Facebook developers panel (https://developers.facebook.com)
|
|  google_client_id               string   Your google client ID.
|  google_client_secret           string   Your google App Secret.
|  google_login_redirect_url   string   URL to redirect back to after login. (do not include base URL)
*/
$config['google_client_id']                = '356581344047-0mr75mv6gn35iulurk3pmuuj6k87icnr.apps.googleusercontent.com';
$config['google_client_secret']            = 'gvpYq2B_VYclqJc9mEhrGdyQ';
$config['google_login_redirect_url']  	   = base_url();
// $config['facebook_logout_redirect_url']   = 'user_authentication/logout';