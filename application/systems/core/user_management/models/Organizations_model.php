<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Organizations_model extends SYSAD_Model 
{

	const DESCENDANTS 			= 'descendants';
	const ANCESTORS 			= 'ancestors';
	const SIBLINGS 				= 'siblings';
	
	private $organizations;

	public $org_parent_fields 		= array(
		'a.org_code', 'a.org_parent', 'a.group_type'
	); // field of org_parent table

	public $org_group_type_fields 	= array(
		'a.group_type', 'a.group_type_name'
	); // field of org_group_type table

	public $org_paths_fields 		= array(
		'a.org_code', 'a.org_parent', 'a.group_type', 'a.org_level', 'a.org_root'
	); // field of oorg_paths table
	
	public function __construct()
	{	
		parent::__construct();
		
		$this->organizations = parent::CORE_TABLE_ORGANIZATIONS;
	}
                
	public function get_org_details($org_code)
	{
		$result 		= array();

		try
		{
			$fields 	= array("*");
			$where 		= array("org_code" => $org_code);
				
			$result 	= $this->select_data($fields, $this->organizations, FALSE, $where);
		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $result;
	}

	public function get_org_details_many(array $org_code)
	{
		$result 		= array();

		try
		{
			$fields 	= array("*");
			$where 		= array("org_code" => array('IN', $org_code));
				
			$result 	= $this->select_data($fields, $this->organizations, TRUE, $where);
		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $result;
	}

	public function get_system_owner( array $extra_where = array() )
	{
		$result 		= array();

		try
		{
			$fields 	= array("*");
			$where 		= array("system_owner" => ENUM_YES);
			$where 		= array_merge( $where, $extra_where );
				
			$result 	= $this->select_data($fields, $this->organizations, FALSE, $where);
		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $result;
	}
	
	public function get_orgs()
	{
		$stmt 		= array();

		try
		{
			$query 	= <<<EOS
				SELECT org_code, IF(org_parent IS NOT NULL, CONCAT("&emsp;&emsp;", name), name) office
				FROM $this->organizations
				GROUP BY org_code, org_parent
EOS;

			$stmt 	= $this->query($query);
		
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $stmt;
	}

	public function get_orgs_all()
	{

		$result 	= array();

		try
		{
			$query 	= <<<EOS
				SELECT 	org_code, org_parent, name, short_name, IF(org_parent IS NOT NULL, CONCAT("&emsp;&emsp;", name), name) office,
						CAST( org_code AS UNSIGNED ) as order_for_org_code,
                        CAST( org_parent AS UNSIGNED ) as order_for_org_parent
				FROM 	$this->organizations
				ORDER 	BY order_for_org_parent, order_for_org_code
EOS;

			$stmt 	= $this->query($query);
			
			$result = $stmt;
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;
	}
	
	
	public function get_other_orgs($exclude)
	{
		$stmt 		= array();

		try
		{
			$query 	= <<<EOS
				SELECT org_code value, name text
				FROM $this->organizations
				WHERE org_code != ?
EOS;
	
			$stmt 	= $this->query($query, array($exclude));
		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $stmt;
		
	}
	
	public function get_org_list($aColumns, $bColumns, $params)
	{
		$stmt 			= array();

		try
		{
			$cColumns 	= array('A-org_code', 'A-name', 'A-email', 'd.name convert_to parent_name', 'A.status convert_to status');
			$fields 	= str_replace(" , ", " ", implode(", ", $aColumns));
			
			$sWhere 	= $this->filtering($cColumns, $params, FALSE);
			$sOrder 	= $this->ordering($bColumns, $params);
			$sLimit 	= $this->paging($params);
			
			$filter_str 	= $sWhere["search_str"];
			$filter_params 	= $sWhere["search_params"];
		
			$query = <<<EOS
				SELECT SQL_CALC_FOUND_ROWS $fields
				FROM $this->organizations A
				LEFT JOIN %s b ON A.org_code = b.org_code
				LEFT JOIN %s d ON b.org_parent = d.org_code
				$filter_str
				GROUP BY A.org_code
	        	$sOrder
	        	$sLimit
EOS;

			$query = sprintf($query, SYSAD_Model::CORE_TABLE_ORGANIZATION_PARENTS, SYSAD_Model::CORE_TABLE_ORGANIZATIONS);
	
			$stmt = $this->query($query, $filter_params);
				
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $stmt;

	}
	
	public function filtered_length($aColumns, $bColumns, $params)
	{
		$stmt 		= array();

		try
		{
			$this->get_org_list($aColumns, $bColumns, $params);
		
			$query 	= <<<EOS
				SELECT FOUND_ROWS() cnt
EOS;
			$stmt 	= $this->query($query, NULL, TRUE, FALSE);
			
		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $stmt;
		
	}
	
	public function total_length()
	{
		$result 	= array();

		try
		{
			$fields = array("COUNT(org_code) cnt");
			
			$result = $this->select_data($fields, $this->organizations, FALSE);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;
	}
		
	public function insert_org($params)
	{
		$org_code 					= NULL;

		try
		{
			$val 					= array();

			$val["org_code"] 		= filter_var($params['org_code'], FILTER_SANITIZE_STRING);
			$val["name"] 			= filter_var($params['org_name'], FILTER_SANITIZE_STRING);
			$val["location_code"] 	= $this->session->location_code;
			$val["created_by"] 		= $this->session->user_id;
			$val["created_date"] 	= date('Y-m-d H:i:s');
			$val['system_owner'] 	= ( ISSET ( $params['system_owner'] ) ) ? ENUM_YES : ENUM_NO;
			
			if(ISSET($params['parent_org_code']) AND !EMPTY($params['parent_org_code']))
			{
				$val["org_parent"] 	= filter_var($params['parent_org_code'], FILTER_SANITIZE_STRING);
			}
			
			if(ISSET($params['org_short_name']) AND !EMPTY($params['org_short_name']))
			{
				$val["short_name"] 	= filter_var($params['org_short_name'], FILTER_SANITIZE_STRING);
			}
			
			if(ISSET($params['website']) AND !EMPTY($params['website']))
			{
				$val["website"] 	= filter_var($params['website'], FILTER_SANITIZE_URL);
			}
			
			if(ISSET($params['email']) AND !EMPTY($params['email']))
			{
				$val["email"] 		= filter_var($params['email'], FILTER_SANITIZE_EMAIL);
			}
			
			if(ISSET($params['phone']) AND !EMPTY($params['phone']))
			{
				$val["phone"]		= filter_var($params['tel_no'], FILTER_SANITIZE_STRING);
			}
			
			if(ISSET($params['fax']) AND !EMPTY($params['fax']))
			{
				$val["fax"] = filter_var($params['fax_no'], FILTER_SANITIZE_STRING);
			}

			if(ISSET($params['organization_type']) AND !EMPTY($params['organization_type']))
			{

				$val["organization_type_id"] 		= $params['organization_type'];
			}
			else
			{
				$val["organization_type_id"] 		= NULL;
			}

			$val['status'] 	= ENUM_NO;

			if( ISSET( $params['status'] ) )
			{
				$val['status'] = ENUM_YES;
			}
			
			$this->insert_data($this->organizations, $val);
				
			$org_code 		= $params['org_code'];
				
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $org_code;
	}

	public function get_parent_orgs( $index = 0, $keyword = '', $org_code = NULL, array $selected_orgs = array(), $no_limit = FALSE )
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{

			$limit 		= "0, 10";
			
			if( !EMPTY( $index ) )
			{
				$limit 	= ( $index - 1 ).', '.( $index * 10 );
			}
			
			$limit_str 	= "LIMIT ".$limit;

			if( !EMPTY( $keyword ) )
			{
				$add_where .= " AND a.name LIKE ? ";
				$extra_val[] = '%'.$keyword.'%';
			}

			if( !EMPTY( $org_code ) )
			{
				$add_where 	.= " AND a.org_code != ? ";
				$extra_val[] = $org_code;
			}

			if( $no_limit )
			{
				$limit_str 	= '';
			}

			if( !EMPTY( $selected_orgs ) )
			{
				if( !EMPTY( $selected_orgs ) )
				{
					$count 				= count( $selected_orgs );
					$placeholder 		= str_repeat('?,', $count);
					$placeholder 		= rtrim($placeholder, ',');

					$add_where .= " AND ( a.status = ? OR a.org_code IN ( $placeholder ) ) ";
					$extra_val[] 	= ENUM_YES;
					$extra_val		= array_merge( $extra_val, $selected_orgs );
				}
			}
			else
			{
				$add_where 	.= " AND a.status = ? ";
				$extra_val[] = ENUM_YES;
			}
 
			$query 		= "
				SELECT 	a.*, GROUP_CONCAT(b.org_parent) as org_parents
				FROM 	%s a 
				LEFT 	JOIN %s b 
					ON 	a.org_code = b.org_code
				WHERE 	1 = 1
					$add_where
					GROUP BY a.org_code
					$limit_str
";
			$query 		= sprintf( $query,
				SYSAD_Model::CORE_TABLE_ORGANIZATIONS,
				SYSAD_Model::CORE_TABLE_ORGANIZATION_PARENTS
			);

			$val 		= array_merge( $val, $extra_val );

			$result 	= $this->query( $query, $val, TRUE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
	
	public function update_org($params, $org_code)
	{
		try
		{
			$val 	= array();
			$where 	= array();
				
			$where["org_code"] 	= filter_var($org_code, FILTER_SANITIZE_STRING);
				
			// $val["org_parent"] 	= (!EMPTY($params['parent_org_code']))? filter_var($params['parent_org_code'], FILTER_SANITIZE_STRING) : NULL;
			// $val["short_name"] 	= filter_var($params['org_short_name'], FILTER_SANITIZE_STRING);
			$val["name"] 		= filter_var($params['org_name'], FILTER_SANITIZE_STRING);

			if(ISSET($params['parent_org_code']) AND !EMPTY($params['parent_org_code']))
			{
				$val["org_parent"] 	= filter_var($params['parent_org_code'], FILTER_SANITIZE_STRING);
			}
			
			if(ISSET($params['org_short_name']) AND !EMPTY($params['org_short_name']))
			{
				$val["short_name"] 	= filter_var($params['org_short_name'], FILTER_SANITIZE_STRING);
			}

			if(ISSET($params['website']) AND !EMPTY($params['website']))
			{

				$val["website"] 	= filter_var($params['website'], FILTER_SANITIZE_URL);
			}

			if(ISSET($params['email']) AND !EMPTY($params['email']))
			{

				$val["email"] 		= filter_var($params['email'], FILTER_SANITIZE_EMAIL);
			}

			if(ISSET($params['tel_no']) AND !EMPTY($params['tel_no']))
			{

				$val["phone"] 		= filter_var($params['tel_no'], FILTER_SANITIZE_STRING);
			}

			if(ISSET($params['fax']) AND !EMPTY($params['fax']))
			{
				$val["fax"] 		= filter_var($params['fax_no'], FILTER_SANITIZE_STRING);
			}

			if(ISSET($params['organization_type']) AND !EMPTY($params['organization_type']))
			{

				$val["organization_type_id"] 		= $params['organization_type'];
			}
			else
			{
				$val["organization_type_id"] 		= NULL;	
			}

			$val["modified_by"]	= $this->session->user_id;
			$val["modified_date"] = date('Y-m-d H:i:s');
			$val['system_owner'] 	= ( ISSET ( $params['system_owner'] ) ) ? ENUM_YES : ENUM_NO;

			$val['status'] 	= ENUM_NO;

			if( ISSET( $params['status'] ) )
			{
				$val['status'] = ENUM_YES;
			}
				
			$this->update_data($this->organizations, $val, $where);
			
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}
	
	public function delete_org($id)
	{
		try
		{
			$where = array("org_code" => $id);
	
			$this->delete_data($this->organizations, $where);	
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}
	
	/**
	 * Use This helper function to get all the org group type
	 *
	 *
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function get_org_group_types()
	{
		$result 		= array();
		$val 			= array();

		try
		{
			$fields 	= implode(', ', $this->org_group_type_fields);

			$query 		= "
				SELECT  $fields
				FROM 	%s a 
";

			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_ORG_GROUP_TYPE );

			$result 	= $this->query( $query, $val);
		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $result;
	}
	
	/**
	 * Use This helper function to insert the org parents
	 *
	 *
	 * @param  $query_str -- required. query string of the values code
	 * @param  $val -- required. values
	 * @throws PDOException
	 * @throws Exception
	 */
	public function insert_org_parents( $query_str, array $val )
	{
		try
		{
			$query 		= "
				INSERT INTO %s 
				VALUES $query_str
";

			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_ORG_PARENTS );

			$this->query( $query, $val , FALSE);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}
	
	/**
	 * Use This helper function to insert the org paths
	 *
	 *
	 * @param  $query_str -- required. query string of the values code
	 * @param  $val -- required. values
	 * @throws PDOException
	 * @throws Exception
	 */
	public function insert_org_paths( $query_str, array $val )
	{
		try
		{
			$query 		= "
				INSERT INTO %s 
				VALUES $query_str
";

			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_ORG_PATHS );

			$this->query( $query, $val , FALSE);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}
	
	/**
	 * Use This helper function to delete the org parents
	 *
	 *
	 * @param  $where -- required. filter
	 * @throws PDOException
	 * @throws Exception
	 */
	public function delete_org_parents( array $where )
	{
		try
		{
			$this->delete_data( SYSAD_Model::CORE_TABLE_ORG_PARENTS, $where );
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}
	
	/**
	 * Use This helper function for the update helper
	 *
	 *
	 * @param  $table -- required. what table
	 * @param  $val -- required. values
	 * @param  $where -- required. filter
	 * @throws PDOException
	 * @throws Exception
	 */
	public function update_helper( $table, array $val, array $where )
	{
		try
		{
			$this->update_data( $table, $val, $where);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}
	
	/**
	 * Use This helper function for the delete helper
	 *
	 *
	 * @param  $table -- required. what table
	 * @param  $where -- required. filter
	 * @throws PDOException
	 * @throws Exception
	 */
	public function delete_helper( $table, array $where )
	{
		try
		{
			$this->delete_data( $table, $where );
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}

	public function get_selected_parent_organizations($org_code)
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{
			$query 		= "
				SELECT 	a.*
				FROM 	%s a 
				WHERE 	a.org_code = ?
					$add_where
";
			$query 		= sprintf( $query,
				SYSAD_Model::CORE_TABLE_ORGANIZATION_PARENTS
			);

			$val[] 		= $org_code;

			$val 		= array_merge( $val, $extra_val );

			$result 	= $this->query( $query, $val, TRUE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
	
	/**
	 * Use This helper function to the delete org paths
	 *
	 *
	 * @param  $org_code -- required. organization code
	 * @param  $group_type -- optional. default value NULL. group type of the organization
	 * @throws PDOException
	 * @throws Exception
	 */
	public function delete_org_paths( $org_code, $group_type = NULL )
	{
		$add_Where 			= "";
		$val 				= array();
		$extra_val 			= array();

		try
		{
			if( !EMPTY( $group_type ) )
			{
				$add_Where 	.= " AND a.group_type = ? ";
				$extra_val[] = $group_type;
			}

			$query 			= "
				DELETE 	a 
				FROM 	%s a
				WHERE 	a.org_code = ?
				$add_Where
";
			$val[] 			= $org_code;

			$query 			= sprintf( $query, SYSAD_Model::CORE_TABLE_ORG_PATHS );

			$val 			= array_merge( $val, $extra_val );

			$this->query( $query, $val, FALSE);

		}
		catch( PDOException $e )  
		{
			throw $e;
		}
	}
	
	
	/*public function select_helper( $fields, $table, array $where = array(), $multiple = FALSE, array $order_arr = array(), array $group_arr = array() )
	{
		$result 			= array();

		try
		{	
			$result 		= $this->select_data( $fields, $table, $multiple, $where, $order_arr, $group_arr );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;

	} */

	/**
	 * Use This helper function to the get the org path details
	 *
	 *
	 * @param  $org_code -- required. organization code
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function get_org_path_details($org_code)
	{
		$result 	= array();

		try
		{
			$result = $this->select_data( $this->org_paths_fields, SYSAD_Model::CORE_TABLE_ORG_PATHS." a", FALSE, array("a.org_code" => $org_code) );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	/**
	 * Use This helper function to the check if the parent org selected still has a parent
	 *
	 *
	 * @param  $org_code -- required. organization code
	 * @param  $group_type -- required. group type of the organization
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function check_parent_org_has_parent( $par_org_code, $group_type )
	{
		$result 			= array();
		$val 				= array();

		try
		{	
			$query 			= "
				SELECT 	b.org_code, b.org_parent, b.group_type,
						c.org_root, c.org_level
				FROM 	%s a 
				JOIN 	%s b ON a.org_code = b.org_code 
				JOIN 	%s c ON b.org_parent = c.org_parent
				AND 	c.group_type = b.group_type
				WHERE 	b.org_code = ?
				AND 	b.group_type = ?
				GROUP 	BY a.org_code, b.org_parent, b.group_type, c.org_level
";
			$val[] 			= $par_org_code;
			$val[] 			= $group_type;

			$query 			= sprintf( $query, SYSAD_Model::CORE_TABLE_ORGANIZATIONS, SYSAD_Model::CORE_TABLE_ORG_PARENTS, SYSAD_Model::CORE_TABLE_ORG_PATHS );
			
			$result 		= $this->query( $query, $val );

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
	
	/**
	 * Use This helper function to the check if the org selected is a root organization
	 *
	 *
	 * @param  $org_root -- required. organization code
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function check_root( $org_root )
	{
		$result 			= array();
		$val 				= array();

		try
		{
			$query 			= "
				SELECT 	COUNT(a.org_root) as check_root
				FROM 	%s a
				WHERE 	a.org_root = ?
";

			$query 			= sprintf( $query, SYSAD_Model::CORE_TABLE_ORG_PATHS );

			$val[] 			= $org_root;

			$result 		= $this->query( $query, $val, TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	/**
	 * Use This helper function to get the children of root organization
	 *
	 *
	 * @param  $org_root -- required. organization code
	 * @param  $group_type -- optional. Default value NULL. group of the organization
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function get_root_children( $org_root, $group_type = NULL )
	{
		$result 			= array();
		$val 				= array();

		$add_where 			= "";
		$add_val 			= array();

		try
		{
			$fields 		= implode(', ', $this->org_paths_fields);

			if( !EMPTY( $group_type ) )
			{
				if( is_array( $group_type ) )
				{
					$count_as_type  = count( $group_type );

					$placeholder 	= str_repeat( '?,', $count_as_type );
					$placeholder 	= rtrim( $placeholder, ',' );

					$add_where 		.= " AND a.group_type IN ( $placeholder ) ";

					$add_val 		= array_merge( $add_val, $group_type );
				}
				else
				{
					
					$add_where 	.= " AND a.group_type = ? ";
					$add_val[] 	= $group_type;
					
				}
			}

			$query 			= "
				SELECT 	$fields
				FROM 	%s a
				WHERE 	a.org_root = ?
				$add_where
";

			$query 			= sprintf( $query, SYSAD_Model::CORE_TABLE_ORG_PATHS );

			$val[] 			= $org_root;

			$val 			= array_merge( $val, $add_val );

			$result 		= $this->query( $query, $val);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	/**
	 * Use This helper function to get the detail org paths of the organization
	 *
	 *
	 * @param  $org_code -- required. organization code
	 * @param  $group_type -- optional. Default value NULL. group of the organization
	 * @param  $for_audit -- optional. Default value FALSE. is the select for audit trail
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function get_root_org_code( $org_code, $group_type = NULL, $for_audit = FALSE )
	{
		$result 			= array();
		$val 				= array();

		$add_where 			= "";
		$add_val 			= array();

		try
		{
			$fields 		= implode(', ', $this->org_paths_fields);

			if( !EMPTY( $group_type ) )
			{
				if( is_array( $group_type ) )
				{
					$count_as_type  = count( $group_type );

					$placeholder 	= str_repeat( '?,', $count_as_type );
					$placeholder 	= rtrim( $placeholder, ',' );

					$add_where 		.= " AND a.group_type IN ( $placeholder ) ";

					$add_val 		= array_merge( $add_val, $group_type );
				}
				else
				{
					
					$add_where 	.= " AND a.group_type = ? ";
					$add_val[] 	= $group_type;
					
				}
			}

			$group_by 		= " GROUP BY a.org_root, a.group_type ";

			if( $for_audit )
			{
				$group_by 	= "";
			}

			$query 			= "
				SELECT 	$fields
				FROM 	%s a
				WHERE 	a.org_code = ?
				$add_where
				$group_by
";


			$query 			= sprintf( $query, SYSAD_Model::CORE_TABLE_ORG_PATHS );

			$val[] 			= $org_code;

			$val 			= array_merge( $val, $add_val );
			
			$result 		= $this->query( $query, $val );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
	
	/**
	 * Use This helper function to get the org paths of the organization under the a specific org root.
	 * Mainly use for audit trail
	 *
	 * @param  $org_code -- required. organization code
	 * @param  $group_type -- optional. Default value NULL. group of the organization
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function get_org_root_audit( $org_code, $group_type = NULL )
	{
		$result 			= array();
		$val 				= array();

		$add_where 			= "";
		$add_val 			= array();

		try
		{
			$fields 		= implode(', ', $this->org_paths_fields);

			if( !EMPTY( $group_type ) )
			{
				if( is_array( $group_type ) )
				{
					$count_as_type  = count( $group_type );

					$placeholder 	= str_repeat( '?,', $count_as_type );
					$placeholder 	= rtrim( $placeholder, ',' );

					$add_where 		.= " AND a.group_type IN ( $placeholder ) ";

					$add_val 		= array_merge( $add_val, $group_type );
				}
				else
				{
					
					$add_where 	.= " AND a.group_type = ? ";
					$add_val[] 	= $group_type;
					
				}
			}

			$query 			= "
				SELECT 	$fields
				FROM 	%s a
				WHERE 	a.org_root = ?
				$add_where
";


			$query 			= sprintf( $query, SYSAD_Model::CORE_TABLE_ORG_PATHS );

			$val[] 			= $org_code;

			$val 			= array_merge( $val, $add_val );
			
			$result 		= $this->query( $query, $val);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
	
	/**
	 * Use This helper function to delete all the organization under a specific org root
	 *
	 * @param  $org_code -- required. organization code
	 * @param  $group_type -- optional. Default value NULL. group of the organization
	 * @throws PDOException
	 * @throws Exception
	 */
	public function delete_root_helper( $org_code, $group_type = NULL )
	{
		$result 			= array();
		$val 				= array();

		$add_where 			= "";
		$add_val 			= array();

		try
		{
			if( !EMPTY( $group_type ) )
			{
				if( is_array( $group_type ) )
				{
					$count_as_type  = count( $group_type );

					$placeholder 	= str_repeat( '?,', $count_as_type );
					$placeholder 	= rtrim( $placeholder, ',' );

					$add_where 		.= " AND group_type IN ( $placeholder ) ";

					$add_val 		= array_merge( $add_val, $group_type );
				}
				else
				{
					
					$add_where 	.= " AND group_type = ? ";
					$add_val[] 	= $group_type;
					
				}
			}

			$query 			= "
				DELETE 	FROM %s
				WHERE 	org_root = ?
				$add_where
";

			$query 			= sprintf( $query, SYSAD_Model::CORE_TABLE_ORG_PATHS );

			$val[] 			= $org_code;

			$val 			= array_merge( $val, $add_val );
			
			$this->query( $query, $val , FALSE);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
	
	/**
	 * Use This helper function to get all the descendant under that specific organization
	 *
	 * @param  $org_code -- required. organization code
	 * @param  $par_str -- required. Org parent query string (NOT IN)
	 * @param  $par_val -- required. Org parent value of the query string
	 * @param  $root_str -- required. Org Root query string (IN)
	 * @param  $root_val -- required. Org Root value of the query string
	 * @param  $group_str -- required. Group type query string (IN)
	 * @param  $group_val -- required. Group type value of the query string
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function get_descendants_update( $org_code, $par_str, array $par_val, $root_str, array $root_val, $group_str, array $group_val )
	{
		$result 			= array();
		$val 				= array();

		try
		{
			$fields 		= implode(', ', $this->org_paths_fields);

			$query 			= "
				SELECT  $fields
				FROM 	%s a
				JOIN 	%s b ON a.org_root = b.org_root
				AND 	a.group_type = b.group_type
				AND 	b.org_code = ?
				AND 	a.org_level > b.org_level
				WHERE 	a.org_parent NOT IN ( $par_str )
				AND 	a.org_root IN ( $root_str )
				AND 	a.group_type IN ( $group_str )
				GROUP 	BY a.org_code, a.org_parent, a.group_type
";

			$val[] 			= $org_code;
			$val 			= array_merge( $val, $par_val );
			$val 			= array_merge( $val, $root_val );
			$val 			= array_merge( $val, $group_val );

			$query 			= sprintf( $query, SYSAD_Model::CORE_TABLE_ORG_PATHS, SYSAD_Model::CORE_TABLE_ORG_PATHS );
			// print_r($query);print_r($val);
			$result 		= $this->query( $query, $val);

		}	
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	/**
	 * Use This helper function to get all org root based on a specific organization
	 *
	 * @param  $org_code -- required. organization code
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function get_root_paths_per_org_code( $org_code )
	{
		$result 			= array();
		$val 				= array();

		try
		{
			$query 			= "
				SELECT 	a.*
				FROM 	%s a 
				WHERE 	a.org_code = ?
				GROUP 	BY a.org_code, a.org_parent, a.group_type
";
			$query 			= sprintf( $query, SYSAD_Model::CORE_TABLE_ORG_PARENTS );

			$val[] 			= $org_code;

			$result 		= $this->query( $query, $val);
		}	
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
	
	/**
	 * Use This helper function to get all relationship of organization based if its by
	 * descendants, ancestors or siblings.
	 *
	 * @param  $org_code -- required. organization code
	 * @param  $type -- required. (DESCENDANTS|ANCETORS|SIBLINGS)
	 * @param  $group_type -- required. group type of the organization
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function get_descendants( $org_code, $type = self::DESCENDANTS, $group_type = NULL, $is_root = FALSE )
	{
		$result 		= array();
		$val 			= array();

		$add_stmt 		= '';
		$extra_val 		= array();

		$where_field	= '';
		$add_where 		= '';
		$add_val 		= array();
		$group_by		= '';

		try
		{
			$type_where_map 		= array(
				self::DESCENDANTS 	=> ' a.org_level > b.org_level ',
				self::ANCESTORS 	=> ' a.org_level < b.org_level ',
				self::SIBLINGS 		=> ' a.org_level = b.org_level '
			);
			
			if($is_root)
			{
				$where_field 	= "b.org_root";
				$group_by		= " GROUP BY a.org_code ";
			}else{
				$where_field 	= "b.org_code";
				$add_where 		.= " AND " .$type_where_map[ $type ];
			}

			if( !EMPTY( $group_type ) )
			{
				$add_where 		.= " AND a.group_type = ? ";
				$add_val[] 		= $group_type;
			}

			if( $type == self::ANCESTORS )
			{
				$union_where 			= "";
				$union_val 				= array();

				if( !EMPTY( $group_type ) )
				{
					$union_where 		.= " AND a.group_type = ? ";
					$union_val[] 		= $group_type;
				}

				$add_stmt 			.= "
					
					UNION ALL

					SELECT 	a.org_code, a.org_code, b.group_type, '0' as org_level, '' as org_root
					FROM 	%s a 
					JOIN 	%s b ON b.org_root = a.org_code
					WHERE 	$where_field = ?
					$union_where
";
				$add_stmt 			= sprintf( $add_stmt, SYSAD_Model::CORE_TABLE_ORGANIZATIONS, SYSAD_Model::CORE_TABLE_ORG_PATHS );

				$extra_val[] 		= $org_code;

				$extra_val 			= array_merge( $extra_val, $union_val );
			}

			

			$fields 				= implode(', ', $this->org_paths_fields);

			$query 		= "
				SELECT 	$fields
                FROM 	%s a
				JOIN 	%s b ON a.org_root = b.org_root
                WHERE 	$where_field = ?
				$add_where	

                $add_stmt
                
                $group_by
";

			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_ORG_PATHS, SYSAD_Model::CORE_TABLE_ORG_PATHS );

			$val[] 		= $org_code;

			$val 		= array_merge( $val, $add_val );
			$val 		= array_merge( $val, $extra_val );
			
			$result 	= $this->query( $query, $val);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	/**
	 * Use This helper function to get all the parent of a specific organization
	 *
	 * @param  $org_code -- required. organization code
	 * @param  $group_type -- optional. default NULL. group type of the organization
	 * @param  $search -- optional. default value array. search parameters usually used in datatable
	 * @param  $params -- optional. default value array. search value of the parameters usually used in datatable
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function get_parents( $org_code, $group_type = NULL, array $search = array(), array $params = array() )
	{
		$result 		= array();
		$val 			= array();

		$add_where 		= '';
		$add_val 		= array();

		try
		{
			$fields 	= implode(', ', $this->org_parent_fields);

			if( !EMPTY( $search ) AND !EMPTY( $params ) )
			{
				$where_val 	= $this->filtering($search, $params, TRUE);

				$add_where 	= $where_val['search_str'];
				$add_val 	= $where_val['search_params'];
			}

			if( !EMPTY( $group_type ) )
			{
				$add_where 	.= " AND a.group_type = ? ";
				$add_val[] 	= $group_type;
			}

			$query 		= "
				SELECT 	$fields, b.name, c.group_type_name, d.name as parent_name
				FROM 	%s a
				JOIN 	%s b ON a.org_code = b.org_code
				JOIN 	%s c ON a.group_type = c.group_type
				JOIN 	%s d ON a.org_parent = d.org_code
				WHERE 	a.org_code = ?
				$add_where
";

			$val[] 		= $org_code;
			$val 		= array_merge( $val, $add_val );

			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_ORG_PARENTS, SYSAD_Model::CORE_TABLE_ORGANIZATIONS, SYSAD_Model::CORE_TABLE_ORG_GROUP_TYPE, SYSAD_Model::CORE_TABLE_ORGANIZATIONS );

			$result 	= $this->query( $query, $val);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
	
	/**
	 * Use This helper function to get the paths of the main organization selected
	 *
	 *
	 * @param  $org_code -- required. organization code
	 * @param  $group_type -- optional. default NULL. group type of the organization
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function get_paths( $org_code, $group_type = NULL )
	{
		$result 		= array();
		$val 			= array();

		$add_where 		= "";
		$extra_val 		= array();

		try
		{
			$fields 	= implode(', ', $this->org_paths_fields);

			if( !EMPTY( $group_type ) )
			{
				if( is_array( $group_type ) )
				{
					$count_as_type  = count( $group_type );

					$placeholder 	= str_repeat( '?,', $count_as_type );
					$placeholder 	= rtrim( $placeholder, ',' );

					$add_where 		.= " AND a.group_type IN ( $placeholder ) ";

					$extra_val 		= array_merge( $extra_val, $group_type );
				}
				else
				{
					
					$add_where 		.= " AND a.group_type = ? ";
					$extra_val[] 	= $group_type;
					
				}

			}

			/*if( !EMPTY( $group_type ) )
			{
				$add_where 	   .= " AND a.group_type = ? ";
				$extra_val[]  	= $group_type;
			}*/

			$query 		= "
				SELECT 	$fields
				FROM 	%s a
				WHERE 	a.org_code = ?
				$add_where
";

			$val[] 		= $org_code;

			$val 		= array_merge( $val, $extra_val );

			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_ORG_PATHS );

			$result 	= $this->query( $query, $val);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
	
	/**
	 * Use This helper function to get the roots of the main organization selected
	 *
	 *
	 * @param  $org_code -- required. organization code
	 * @param  $group_type -- optional. default NULL. group type of the organization
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function get_roots( $org_code, $group_type = NULL )
	{
		$result 		= array();
		$val 			= array();

		$add_val 		= array();
		$add_where 		= "";

		try
		{
			$fields 	= implode(', ', $this->org_paths_fields);

			if( !EMPTY( $group_type ) )
			{
				$add_where 	= " AND a.group_type = ? ";
				$add_val[] 	= $group_type;
			}

			$query 		= "
				SELECT 	$fields
				FROM 	%s a
				WHERE 	a.org_code = ?
				$add_where
				GROUP 	BY a.org_root
";

			$val[] 		= $org_code;
			$val 		= array_merge( $val, $add_val );

			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_ORG_PATHS );

			$result 	= $this->query( $query, $val);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
	
	/**
	 * Use This helper function to check if group type has a root organization
	 *
	 *
	 * @param  $group_type -- required. group type of the organization
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function check_has_already_parent_helper( $group_type )
	{
		$result 		= array();

		$where 			= array();

		try
		{
			$where['group_type']	= $group_type;
			$fields 				= array('COUNT(org_code) as check_has_already_parent, org_root');

			$result 	= $this->select_data(
				$fields,
				SYSAD_Model::CORE_TABLE_ORG_PATHS,
				FALSE,
				$where
			);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
	
	/**
	 * Use This helper function to check if org parent is a root organization
	 *
	 *
	 * @param  $org_parent -- required. organization code
	 * @param  $group_type -- required. group type of the organization
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function check_if_root_helper( $org_parent, $group_type )
	{
		$result 		= array();
		$where 			= array();

		try
		{
			$where['org_code']		= $org_parent;
			$where['group_type']	= $group_type;

			$fields 				= array('COUNT(org_code) as check_if_root');

			$result 	= $this->select_data(
				$fields,
				SYSAD_Model::CORE_TABLE_ORG_PATHS,
				FALSE,
				$where
			);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
	
	/**
	 * Use This helper function to check if org path already exists
	 *
	 *
	 * @param  $where -- required. filter
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function check_org_path_exisits( array $where )
	{
		$result 		= array();

		try
		{
			$fields 	= array('COUNT(org_code) as check_exists');

			$result 	= $this->select_data(
				$fields,
				SYSAD_Model::CORE_TABLE_ORG_PATHS,
				FALSE,
				$where
			);
		}	
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function insert_helper( $table, array $val )
	{
		$id 	= NULL;

		try
		{
			$id 	= $this->insert_data( $table, $val, TRUE, TRUE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $id;
	}
	
	/**
	 * Use This helper function to get the org path details
	 *
	 *
	 * @param  $where -- required. filter
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function get_org_path_details_helper( array $where )
	{
		$result 		= array();

		try
		{
			$result 	= $this->select_data(
				$this->org_paths_fields,
				SYSAD_Model::CORE_TABLE_ORG_PATHS.' a',
				FALSE,
				$where
			);
		}		
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
	
	/**
	 * Use This helper function to check if the org code is valid
	 *
	 *
	 * @param  $where -- required. filter
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function check_valid_org_code_helper( array $where )
	{
		$result 		= array();

		try
		{
			$fields 			= array('COUNT(org_code) as check_org');
			
			$result 			= $this->select_data(
				$fields,
				SYSAD_Model::CORE_TABLE_ORGANIZATIONS,
				FALSE,
				$where
			);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
	
	/**
	 * Use This helper function to check if the group type is valid
	 *
	 *
	 * @param  $where -- required. filter
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function check_valid_group_type_helper( array $where )
	{
		$result 		= array();

		try
		{
			$fields 		= array('COUNT(group_type) as check_grp');

			$result 		= $this->select_data(
				$fields,
				SYSAD_Model::CORE_TABLE_ORG_GROUP_TYPE,
				FALSE,
				$where
			);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}		

	public function get_orgs_all_lazy($index = 0, $keyword = '')
	{

		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{

			$limit 		= "0, 10";
			
			if( !EMPTY( $index ) )
			{
				$limit 	= ( $index - 1 ).', '.( $index * 10 );
			}
			
			$limit_str 	= "LIMIT ".$limit;

			if( !EMPTY( $keyword ) )
			{
				$add_where .= " AND ( name Like ? OR short_name like ? ) ";
				$extra_val[] = '%'.$keyword.'%';
				$extra_val[] = '%'.$keyword.'%';
			}

			$query 	= <<<EOS
				SELECT 	a.org_code, a.org_parent, a.name, a.short_name, IF(a.org_parent IS NOT NULL, CONCAT("&emsp;&emsp;", a.name), name) office,
						CAST( a.org_code AS UNSIGNED ) as order_for_org_code,
                        CAST( a.org_parent AS UNSIGNED ) as order_for_org_parent,
                        GROUP_CONCAT(b.org_parent) as org_parents
				FROM 	$this->organizations a
				LEFT 	JOIN %s b 
					ON 	a.org_code = b.org_code
				WHERE 	1 = 1
				$add_where
				GROUP 	BY a.org_code
				ORDER 	BY order_for_org_parent, order_for_org_code
				$limit_str
EOS;

			$val 		= array_merge( $val, $extra_val );

			$replacements 	= array(
				SYSAD_Model::CORE_TABLE_ORGANIZATION_PARENTS
			);

			$query 			= preg_replace_callback('/[\%]s/', function() use (&$replacements)
			{
				 return array_shift($replacements);
			}, $query);



			$stmt 	= $this->query($query, $val, TRUE);
			
			$result = $stmt;
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;
	}

	public function get_orgs_details_with_parents($org_code)
	{

		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{


			$query 	= <<<EOS
				SELECT 	a.org_code, a.org_parent, a.name, a.short_name, IF(a.org_parent IS NOT NULL, CONCAT("&emsp;&emsp;", a.name), name) office, a.name as text,
						CAST( a.org_code AS UNSIGNED ) as order_for_org_code,
                        CAST( a.org_parent AS UNSIGNED ) as order_for_org_parent,
                        GROUP_CONCAT(b.org_parent) as org_parents,
                        a.organization_type_id
				FROM 	$this->organizations a
				LEFT 	JOIN %s b 
					ON 	a.org_code = b.org_code
				WHERE 	1 = 1
				AND 	a.org_code = ?
				$add_where
				GROUP 	BY a.org_code
				ORDER 	BY order_for_org_parent, order_for_org_code
EOS;

			$val[] 		= $org_code;

			$val 		= array_merge( $val, $extra_val );

			$replacements 	= array(
				SYSAD_Model::CORE_TABLE_ORGANIZATION_PARENTS
			);

			$query 			= preg_replace_callback('/[\%]s/', function() use (&$replacements)
			{
				 return array_shift($replacements);
			}, $query);



			$stmt 	= $this->query($query, $val, TRUE, FALSE);
			
			$result = $stmt;
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;
	}


	public function get_orgs_details_with_parents_many(array $org_codes)
	{

		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{

			if( !EMPTY( $org_codes ) )
			{
				$count 				= count( $org_codes );
				$placeholder 		= str_repeat('?,', $count);
				$placeholder 		= rtrim($placeholder, ',');

				$add_where .= " AND a.org_code IN ( $placeholder ) ";
				$extra_val		= array_merge( $extra_val, $org_codes );
			}


			$query 	= <<<EOS
				SELECT 	a.org_code, a.org_parent, a.name, a.short_name, IF(a.org_parent IS NOT NULL, CONCAT("&emsp;&emsp;", a.name), name) office, a.name as text,
						CAST( a.org_code AS UNSIGNED ) as order_for_org_code,
                        CAST( a.org_parent AS UNSIGNED ) as order_for_org_parent,
                        GROUP_CONCAT(b.org_parent) as org_parents,
                        a.organization_type_id
				FROM 	$this->organizations a
				LEFT 	JOIN %s b 
					ON 	a.org_code = b.org_code
				WHERE 	1 = 1
				$add_where
				GROUP 	BY a.org_code
				ORDER 	BY order_for_org_parent, order_for_org_code
EOS;


			$val 		= array_merge( $val, $extra_val );

			$replacements 	= array(
				SYSAD_Model::CORE_TABLE_ORGANIZATION_PARENTS
			);

			$query 			= preg_replace_callback('/[\%]s/', function() use (&$replacements)
			{
				 return array_shift($replacements);
			}, $query);



			$stmt 	= $this->query($query, $val, TRUE);
			
			$result = $stmt;
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;
	}

	public function get_org_types()
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{

			$query 		= "
				SELECT 	a.*
				FROM 	%s a 
";

			$query 		= sprintf( $query, SYSAD_Model::CORE_ORGANIZATION_TYPES );

			$result 	= $this->query($query, $val, TRUE);

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
}
