<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Checkmail extends MX_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->library('email');
	}
	
	public function index()
	{
		$config = array(
			'protocol' => 'smtp',
			'smtp_host' => 'ssl://smtp.gmail.com',
			'smtp_pass' => 'technofreeze',
			'smtp_port' => 465,
			'smtp_user' => 'kebwaitforit@gmail.com',
			'mailtype' => 'html',
			//'charset' => 'utf-8',
			'charset' => 'iso-8859-1',
			'validate' => TRUE,
			'smtp_timeout' => 60,
		);
/*
+----------------+----------------+------------------+------------------------+---------------+
| CONF_EMAIL     | SMTP           | SMTP_REPLY_EMAIL | no-reply@asiagate.com  |             1 |
| CONF_HOST      | SMTP           | SMTP_HOST        | ssl://smtp.gmail.com   |             1 |
| CONF_NAME      | SMTP           | SMTP_REPLY_NAME  | No-reply               |             1 |
| CONF_PASSWORD  | SMTP           | SMTP_PASS        | technofreeze           |             1 |
| CONF_PORT      | SMTP           | SMTP_PORT        | 465                    |             1 |
| CONF_PROTOCOL  | SMTP           | PROTOCOL         | smtp                   |             1 |
| CONF_USERNAME  | SMTP           | SMTP_USER        | kebwaitforit@gmail.com |             1 |
+----------------+----------------+------------------+------------------------+---------------+
*/
		
/*	
error_reporting(E_ALL);

echo '<pre>';
var_dump($fso = fsockopen("ssl://smtp.gmail.com", 465, $errno, $errstr));
var_dump($errno);
var_dump($errstr);

var_dump(is_resource($fso));

	
		$fp = @fsockopen('ssl://smtp.gmail.com', 465, $errno, $errstr, 300);
		//$fp = @fsockopen('tls://smtp.gmail.com', 587, $errno, $errstr, 300);
		
		if ( ! is_resource($fp)) {
			echo sprintf("no resource. error no: %s, error: %s", $errno, $errstr);
			exit;
		}

		echo 'has resource'; exit;
	/*	
if (!$fp) {
    echo "$errstr ($errno)<br />\n";
} else {
    $out = "GET / HTTP/1.1\r\n";
    $out .= "Host: www.example.com\r\n";
    $out .= "Connection: Close\r\n\r\n";
    fwrite($fp, $out);
    while (!feof($fp)) {
        echo fgets($fp, 128);
    }
    fclose($fp);
}
exit;
*/
		
		$this->email->initialize($config);
		$this->email->set_newline("\r\n");
		
		$this->email->clear();
		
		$this->email->from($config['smtp_user'], 'LGU360 Support');
		$this->email->to('rfiguracion.asiagate@gmail.com');
					
		$this->email->subject('test 123');
		$this->email->message('test');
		
		echo '<pre>';
		echo $this->email->send() . "\n";
		echo $this->email->print_debugger(array('headers', 'subject', 'body'));
		exit;
	}
}
