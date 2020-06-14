<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Files_model extends SYSAD_Model {
	
	private $files;
	private $file_versions;
	private $users;
                
	public function __construct()
	{
		parent::__construct();
		
		$this->files = parent::CORE_TABLE_FILE;
		$this->file_versions = parent::CORE_TABLE_FILE_VERSIONS;
		$this->users = parent::CORE_TABLE_USERS;
	}

	public function get_files_list( array $columns, array $filter, array $order_arr, array $params, $user_id )
	{
		$val 					= array();
		$result 				= array();
		$filter_str 			= '';
		$filter_params			= '';

		$add_where 				= '';
		$extra_val 			 	= array();		

		try
		{
			$fields 			= str_replace( " , " , " ", implode( ", ", $columns ) );

			$where 				= $this->filtering( $filter, $params, TRUE );

			$order 				= $this->ordering( $order_arr, $params );

			$limit 				= $this->paging($params);

			$filter_str 		= $where['search_str'];

			$filter_params 		= $where['search_params'];

			if( ISSET( $params['file_type'] ) )
			{
				$add_where 	.= " AND a.file_type = ? ";
				$extra_val[] = $params['file_type'];
			}

			/*if( !ISSET( $params['sEcho'] ) OR ( ISSET( $params['sEcho'] ) AND intval($params['sEcho'] ) == 1 ) )
			{
				$order 		= " ORDER BY a.created_date DESC ";
			}*/

			$query 		= "
				SELECT 	SQL_CALC_FOUND_ROWS $fields
				FROM 	%s a 
				JOIN 	%s b 
				ON 		a.created_by = b.user_id
				LEFT 	JOIN %s c 
				ON 		a.modified_by = c.user_id
				LEFT 	JOIN (
					SELECT	sub_a.file_version_id,
							sub_a.file_id,
							GROUP_CONCAT( sub_a.file_name ORDER BY sub_a.file_version_id ) as version_file_name,
							GROUP_CONCAT( sub_a.version ORDER BY sub_a.file_version_id ) as version, 
							GROUP_CONCAT( sub_a.file_version_id ORDER BY sub_a.file_version_id ) as file_version_ids
					FROM 	%s sub_a
				 	GROUP 	BY sub_a.file_id
				) d 	ON a.file_id = d.file_id
				LEFT 	JOIN (
					SELECT 	a.file_id, GROUP_CONCAT( a.user_id ) as user_id,  a.visibility_id 
                    FROM 	%s a
                    GROUP 	BY a.file_id
                ) m 	ON a.file_id = m.file_id
				WHERE 	1 = 1
				$add_where
				$filter_str
				HAVING 
				(
					( a.created_by = ? )
					OR ( visibility_id = ? )
				 	OR ( visibility_id = ? AND a.created_by =  ? )
				 	OR ( ( visibility_id = ? OR visibility_id = ? ) AND FIND_IN_SET( ?,  visible_user_id) )
				)
				$order
				$limit
";

			$replacements 	= array(
				SYSAD_Model::CORE_TABLE_FILE,
			   	SYSAD_Model::CORE_TABLE_USERS,
			   	SYSAD_Model::CORE_TABLE_USERS,
			   	SYSAD_Model::CORE_TABLE_FILE_VERSIONS,
			   	SYSAD_Model::CORE_TABLE_FILE_VISIBILITY
			);

			$query 			= preg_replace_callback('/[\%]s/', function() use (&$replacements)
			{
				 return array_shift($replacements);
			}, $query);


			$val 		= array_merge( $val, $extra_val );

			$val 		= array_merge( $val, $filter_params );

			$val[] 		= $user_id;
			$val[] 		= VISIBLE_ALL;
			$val[] 		= VISIBLE_ONLY_ME;
			$val[] 		= $user_id;
			$val[] 		= VISIBLE_GROUPS;
			$val[] 		= VISIBLE_INDIVIDUALS;
			$val[] 		= $user_id;
			
			$result['aaData'] 	= $this->query( $query, $val);

			$query2 			= "
				SELECT 	FOUND_ROWS() filtered_length
";

			$result['filtered_length'] 	= $this->query( $query2, array(), TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function insert_files( array $val )
	{
		$id 	= NULL;

		try
		{
			$id = $this->insert_data( SYSAD_Model::CORE_TABLE_FILE, $val, TRUE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $id;
	}

	public function update_files( array $val, array $where )
	{
		try
		{
			$this->update_data( SYSAD_Model::CORE_TABLE_FILE, $val, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function delete_file( array $where )
	{
		try
		{
			$this->delete_data( SYSAD_Model::CORE_TABLE_FILE, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function get_specific_file( $id )
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query 	= "
				SELECT	a.file_id,
						a.display_name, 
						a.file_name,
						a.original_name,
						a.description, 
						a.file_type,
						a.module_code,
						a.file_extension,
						b.version,
						a.created_by
				FROM 	%s a
				LEFT 	JOIN (
					SELECT	sub_a.file_version_id,
							sub_a.file_id,
							GROUP_CONCAT( sub_a.file_name ORDER BY sub_a.file_version_id ) as version_file_name,
							GROUP_CONCAT( sub_a.version ORDER BY sub_a.file_version_id ) as version, 
							GROUP_CONCAT( sub_a.file_version_id ORDER BY sub_a.file_version_id ) as file_version_ids
					FROM 	%s sub_a
				 	GROUP 	BY sub_a.file_id
				) b 	ON a.file_id = b.file_id
				WHERE 	a.file_id = ?
";

			$query 	= sprintf( $query, SYSAD_Model::CORE_TABLE_FILE, SYSAD_Model::CORE_TABLE_FILE_VERSIONS );

			$val[] 	= $id;

			$result = $this->query( $query, $val, TRUE, FALSE );
		}		
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
	
	public function get_files($order_arr = array())
	{
		try
		{
			$order_by = "";
			
			if(!EMPTY($order_arr)){
				$order_by .= " ORDER BY ";
					
				foreach ($order_arr as $a => $b):
					$order_by .= $a." ".$b.", ";
				endforeach;

				$order_by = rtrim($order_by, ", ");
			} else {
				$order_by = " ORDER BY created_date DESC";
			}
			$query = <<<EOS
				SELECT *
				FROM $this->files
				$order_by
EOS;
			$stmt = $this->query($query);
			
			return $stmt;
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
	
	public function get_file_details($file_id)
	{
		try
		{
			$fields = array("*");
			$where = array("file_id" => $file_id);
				
			return $this->select_data($fields, $this->files, TRUE, $where);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}	
	}
	
	public function get_file_version_details($file_version_id = NULL, $file_id = NULL)
	{
		try
		{
			$where = array();
			$fields = array("*");
			
			if(!IS_NULL($file_version_id))
				$where["file_version_id"] = $file_version_id;
			
			if(!IS_NULL($file_id))
				$where["file_id"] = $file_id;
				
			return $this->select_data($fields, $this->file_versions, TRUE, $where);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
	
	public function get_file_versions($file_id)
	{
		try
		{
			$val = array($file_id, $file_id);
			
			$query = <<<EOS
				SELECT file_id, file_version_id, file_name, display_name, version
				FROM $this->file_versions
				WHERE file_id = ?
				UNION
				SELECT file_id, NULL id, file_name, display_name, 1 version
				FROM $this->files
				WHERE file_id = ?
				ORDER BY version DESC
EOS;
			$stmt = $this->query($query, $val);
			
			return $stmt;
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
	
	/*public function insert_files($file, $params)
	{
		try
		{
			$val = array();
			
			$val["cy"] = filter_var($params['file_budget_year'], FILTER_SANITIZE_NUMBER_INT);
			$val["file_name"] = filter_var($file, FILTER_SANITIZE_STRING);
			$val["display_name"] = filter_var($file, FILTER_SANITIZE_STRING);
			$val["description"] = filter_var($params['file_description'], FILTER_SANITIZE_STRING);
			$val["created_by"] = $this->session->user_id;
			$val["created_date"] = date('Y-m-d H:i:s');
			
			$file_id = $this->insert_data($this->files, $val, TRUE);
			
			return $file_id;
			
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}*/
	
	/*public function update_files($params)
	{
		try
		{
			$val = array();
			$where = array();
			
			$is_versioned = !EMPTY($params['file_version_id']) ? 1 : 0;
			$table = ($is_versioned) ? $this->file_versions : $this->files;
			
			$val["display_name"] = filter_var($params['file_display_name'], FILTER_SANITIZE_STRING);
			$val["description"] = filter_var($params['file_description'], FILTER_SANITIZE_STRING);
			$val["modified_by"]	= $this->session->user_id;
			
			if(!$is_versioned){
				$val["cy"] = filter_var($params['file_budget_year'], FILTER_SANITIZE_NUMBER_INT);
			}else{
				$where["file_version_id"] = filter_var($params["file_version_id"], FILTER_SANITIZE_NUMBER_INT);
			}
			
			$where["file_id"] = filter_var($params["id"], FILTER_SANITIZE_NUMBER_INT);
			
			$this->update_data($table, $val, $where);
			
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}*/
	
/*	public function delete_file($id, $file_version_id = NULL)
	{
		try
		{
			$where = array();
				
			$where['file_id'] = $id;
			$table = $this->files;
			
			if(!IS_NULL($file_version_id)){
				$where['file_version_id'] = $file_version_id;
				$table = $this->file_versions;
			}
				
			$this->delete_data($table, $where);
				
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}*/
	
	public function insert_file_version($params)
	{
		try
		{
			$val = array();
			
			$version = ISSET($params['minor_revision_flag']) ? 0.1 : 1;
			$id = $params['file_id'];
			$data = $this->get_latest_attachments($id);
			$version += ISSET($params['minor_revision_flag']) ? $data['version'] : floor($data['version']);
			
			$val["file_id"] = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
			$val["file_name"] = filter_var($params['file_version'], FILTER_SANITIZE_STRING);
			$val["display_name"] = filter_var($params['file_version'], FILTER_SANITIZE_STRING);
			$val["description"] = filter_var($params['file_version_description'], FILTER_SANITIZE_STRING);
			$val["minor_revision_flag"] = ISSET($params['minor_revision_flag']) ? $params['minor_revision_flag'] : 0;
			$val["version"] = $version;
			$val["created_by"] = $this->session->user_id;
			$val["created_date"] = date('Y-m-d H:i:s');
			
			$file_version_id = $this->insert_data($this->file_versions, $val, TRUE);
			
			return $file_version_id;
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
	
	public function get_latest_attachments($file_id = NULL, $file_version_id = NULL, $order_arr = array())
	{
		try
		{
			$val = array();
			
			$where = "1";
			
			if(!IS_NULL($file_id)){
				$where.= " AND A.file_id = ? ";
				$val[] = $file_id;
			}
			
			if(!IS_NULL($file_version_id)){
				$where.= " AND B.file_version_id = ? ";
				$val[] = $file_version_id;
			}
			
			$order_by = "";
			
			if(!EMPTY($order_arr)){
				$order_by .= " ORDER BY ";
					
				foreach ($order_arr as $a => $b):
					$order_by .= $a." ".$b.", ";
				endforeach;

				$order_by = rtrim($order_by, ", ");
			} else {
				$order_by = " ORDER BY created_date DESC";
			}
			
			$query = <<<EOS
				SELECT A.file_id, B.file_version_id, IF(B.version IS NULL, 1, B.version) version, 
					CONCAT(C.fname, ' ', C.lname) created_by,
					IF(B.version IS NULL, A.created_date, B.created_date) created_date,
					IF(B.version IS NULL, A.file_name, B.file_name) file_name,
					IF(B.version IS NULL, A.display_name, B.display_name) display_name,
					IF(B.version IS NULL, A.description, B.description) description
				FROM $this->files A
				LEFT JOIN $this->file_versions B ON A.file_id = B.file_id
					AND B.version = (SELECT MAX(version) FROM $this->file_versions WHERE file_id = A.file_id)
				INNER JOIN $this->users C ON ((IF(B.version IS NULL, A.created_by, B.created_by)) = C.user_id)
				WHERE $where
				$order_by
EOS;
			if(IS_NULL($file_id)){
				$stmt = $this->query($query, $val);
			} else {
				$stmt = $this->query($query, $val, TRUE, FALSE);
			}
			
			return $stmt;
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
			
}