<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CBS_Model extends Common_Model {

	protected static $dsn = DB_SCBS;
	protected static $system = SYSTEM_CBS;
	
	public function __construct() 
	{
		parent::__construct();
	}

	/*
	| General Ledger Tables
	*/
	const CBS_TABLE_GL_TYPES     = 'gl_types';
	const CBS_TABLE_GL_SORTS  	 = 'gl_sorts';
	const CBS_TABLE_GL_ACCOUNTS  = 'gl_accounts';
	const CBS_TABLE_TRANSACTIONS = 'gl_transactions';

	/*
	| Config or Reference tables
	*/
	const CBS_TABLE_CONFIG_HOLIDAYS     = 'config_holidays';
	const CBS_TABLE_CONFIG_CIVIL_STATUS = 'config_civil_status';
	const CBS_TABLE_CONFIG_TITLES     	= 'config_titles';
	const CBS_TABLE_CONFIG_BLOOD_TYPES  = 'config_blood_types';
	const CBS_TABLE_CONFIG_RELIGIONS    = 'config_religions';
	const CBS_TABLE_CONFIG_RELATIONSHIP_TYPES = 'config_relationship_types';

	/*
	| Location Tables
	*/
	const CBS_TABLE_LOCATION_REGIONS    = 'location_regions';
	const CBS_TABLE_LOCATION_PROVINCES  = 'location_provinces';
	const CBS_TABLE_LOCATION_CITYMUNI  = 'location_citymuni';
	const CBS_TABLE_LOCATION_BARANGAYS  = 'location_barangays';

	/*
	| Branch Tables
	*/
	const CBS_TABLE_BRANCHES     	 = 'branches';
	const CBS_TABLE_BRANCH_SETUPS    = 'branch_setups';
	const CBS_TABLE_BRANCH_HOLIDAYS  = 'branch_holidays';
	
	/*
	| Customers Tables
	*/
	const CBS_TABLE_CUSTOMERS     	 	= 'customers';
	const CBS_TABLE_CUSTOMER_ADDRESSES  = 'customer_addresses';
	const CBS_TABLE_CUSTOMER_CONTACTS   = 'customer_contacts';

}
