<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SOS_Model extends Base_Model {

	protected static $dsn = DB_SOS;
	protected static $system = SYSTEM_SOS;
	
	public function __construct() 
	{
		parent::__construct();
	}

	// Main Tables
	const SOS_TABLE_ORDERS         		= 'orders';
	const SOS_TABLE_ORDER_DETAILS  		= 'order_details';
	const SOS_TABLE_PRODUCTS       		= 'products';
	const SOS_TABLE_CUSTOMERS       	= 'customers';
	const SOS_TABLE_CUSTOMER_ADDRESSES  = 'customer_addresses';
	
	// locations
	const SOS_TABLE_LOCATION_REGIONS     = 'location_regions';
	const SOS_TABLE_LOCATION_PROVINCES   = 'location_provinces';
	const SOS_TABLE_LOCATION_CITYMUNI    = 'location_citymuni';

	//param tables
	const SOS_PARAM_BILLING_METHODS    	= 'param_billing_methods';
	const SOS_PARAM_SALES_CHANNELS   	= 'param_sales_channels';
	const SOS_PARAM_ORDER_STATUSES      = 'param_order_statuses';
	const SOS_PARAM_DELIVERY_METHODS    = 'param_delivery_methods';
	const SOS_PARAM_DELIVERY_DATE    	= 'param_delivery_date';


}
