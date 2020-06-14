<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MAIN_Model extends CI_Model {
                
	public function __construct() {
		
		parent::__construct();
	}	
	
	/* FOR DATATABLE */
	protected function filtering($aColumns, $params, $has_where)
	{

		$sWhere_arr = array();
		$sWhere = "";
		$search_params = array();
		
		if (ISSET($params['sSearch']) && $params['sSearch'] != "")
		{
			
			$sWhere = ($has_where)? " AND (" : "WHERE (";
			
			for ($i=0; $i<count($aColumns); $i++)
			{
				if (ISSET($params['bSearchable_'.$i]) && $params['bSearchable_'.$i] == "true")
				{
					$sWhere .= "LOWER(".$aColumns[$i].") LIKE ? OR ";
					
					$search_params[] = "%".strtolower(filter_var($params['sSearch'], FILTER_SANITIZE_STRING))."%";
				}
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
			
		}
			
		/* Individual column filtering */
		for ($i=0 ; $i<count($aColumns); $i++)
		{
			if (ISSET($params['bSearchable_'.$i]) && $params['bSearchable_'.$i] == "true" && $params['sSearch_'.$i] != '')
			{
				if ($sWhere == "")
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= $aColumns[$i]." LIKE ? ";
				
				$search_params[] = "%".strtolower(filter_var($params['sSearch'], FILTER_SANITIZE_STRING))."%";
			}
			
		}
		
		$sWhere_arr["search_str"] = $sWhere;
		$sWhere_arr["search_params"] = $search_params;
	
		return $sWhere_arr;
	}
		
	protected function ordering($aColumns, $params)
	{
		$sOrder = "";
		if (ISSET($params['iSortCol_0']))
		{
			$sOrder = "ORDER BY  ";
			for ($i=0 ; $i<intval( $params['iSortingCols'] ) ; $i++)
			{
				if ($params[ 'bSortable_'.intval($params['iSortCol_'.$i]) ] == "true")
				{
					$sOrder .= $aColumns[ intval( $params['iSortCol_'.$i] ) ]."
					".($params['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
				}
			}
			 
			$sOrder = substr_replace( $sOrder, "", -2 );
			if ( $sOrder == "ORDER BY" )
				$sOrder = "";
		}
		
		return $sOrder;
	}	
	
	protected function paging($params)
	{
		$sLimit = "";
		if (ISSET($params['iDisplayStart']) && $params['iDisplayLength'] != '-1')
		{
			$sLimit = "LIMIT ".intval( $params['iDisplayStart'] ).", ".
				intval( $params['iDisplayLength'] );
		}
	
		return $sLimit;
	}
	/* FOR DATATABLE */
				
}