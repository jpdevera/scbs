<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Pdf {

	protected $phpVersion;

	public function __construct()
	{
		$CI = & get_instance();

		$this->phpVersion 	= phpversion();

		if( $this->phpVersion >= 7.2 )
		{
			// require_once APPPATH . 'third_party/MPDF7/vendor/autoload.php';
			require_once APPPATH . 'third_party/MPDF6/autoload.php';
		}
		else
		{
			require_once APPPATH . 'third_party/MPDF6/autoload.php';
		}
		//log_message('Debug', 'mPDF class is loaded.');
	}

	public function load($params=NULL)
	{
		if( $this->phpVersion >= 7.2 )
		{
			/*if ($params == NULL)
			{
				$params = array('en-GB-x', 'A4', '', '', 10,10,10,10,10,10,'L');
				// $params = '"en-GB-x","A4","","",10,10,10,10,10,10, "L"';
			}
			
			//$params = '"c","A4-L","","",10,10,20,20,30,30, "L"';
			
			return new Mpdf\Mpdf($params);*/

			if ($params == NULL)
			{
				$params = '"en-GB-x","A4","","",10,10,10,10,10,10, "L"';
			}

			return new mPDF($params);
		}
		else
		{
			if ($params == NULL)
			{
				$params = '"en-GB-x","A4","","",10,10,10,10,10,10, "L"';
			}

			return new mPDF($params);
		}
	}
	
	public function create()
	{
		$var 	= func_get_args();
			
		if( $this->phpVersion >= 7.2 )
		{
			/*$pdf 	= new Mpdf\Mpdf($var);

			return $pdf;*/
			$pdf = new ReflectionClass('mPDF');
			
			return $pdf->newInstanceArgs($var);
		}
		else
		{
			$pdf = new ReflectionClass('mPDF');
			
			return $pdf->newInstanceArgs($var);
		}
	}
}
