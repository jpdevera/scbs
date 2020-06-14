<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BOOTCAMP_Model extends Base_Model 
{	

	protected static $dsn = DB_BOOTCAMP;
	protected static $system = SYSTEM_BOOTCAMP;
	
	//FOR DB MAIN
	/*public $db_bootcamp		= DB_BOOTCAMP;
	
	//FOR DB CORE
	public $db_core			= DB_CORE;*/

	/*START : BOOTCAMP TABLES*/

	const BOOTCAMP_TABLE_SAMPLE_21			= 'sample_21';
	const BOOTCAMP_TABLE_SAMPLE_8			= 'sample_8';
	
	/*END : BOOTCAMP TABLES*/

}