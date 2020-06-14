<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Widgets_model extends SYSAD_Model 
{	
	private $area_columns;
	private $areas;
	private $widget_roles;
	private $widget_types;
	private $widgets;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->area_columns 	= parent::CORE_TABLE_AREA_COLUMNS;
		$this->areas 			= parent::CORE_TABLE_AREAS;
		$this->widget_roles 	= parent::CORE_TABLE_WIDGET_ROLES;
		$this->widget_types 	= parent::CORE_TABLE_WIDGET_TYPES;
		$this->widgets		 	= parent::CORE_TABLE_WIDGETS;
	}
	
	public function get_area_layout()
	{
		$result 	= array();
		$val 		= array(
			$this->session->user_main_role,
			STATUS_ACTIVE
		);
		
		try
		{
			$query 	= <<<EOS
				SELECT 
					A.area_code,
					A.area_name,
					A.description,
					B.area_column_id,
					B.column,
					B.description AS column_description,
					B.size,
					C.widget_id,
					C.widget_name
				FROM $this->areas A
				JOIN $this->area_columns B 
					ON A.area_code = B.area_code
				LEFT JOIN (
					SELECT 
						WR.*, 
						W.widget_name
					FROM $this->widget_roles WR, $this->widgets W
				    WHERE WR.widget_id = W.widget_id
				) C ON B.area_column_id = C.area_column_id AND C.role_code = ?
				WHERE A.status = ?
				ORDER BY A.sort_order ASC, B.column ASC, C.sort_order ASC
EOS;
			
			$stmt 	= $this->query($query, $val);
			
			$result = $stmt;
		}
		catch (PDOException $e)
		{
			throw $e;
		}
		
		return $result;
	}
	
	public function get_widget_roles($role_code)
	{
		$result 	= array();
		$val 		= array($role_code);
		
		try
		{
			$query 	= <<<EOS
				SELECT B.widget_name, A.*, C.area_code, C.column, C.description, C.size
				FROM $this->widget_roles A, $this->widgets B, $this->area_columns C
				WHERE A.widget_id = B.widget_id
				AND A.area_column_id = C.area_column_id
				AND A.role_code = ?
				ORDER BY C.area_code ASC, C.column ASC, A.sort_order ASC
EOS;
			
			$stmt 	= $this->query($query, $val);
			
			$result = $stmt;
		}
		catch (PDOException $e)
		{
			throw $e;
		}
		
		return $result;
	}
}