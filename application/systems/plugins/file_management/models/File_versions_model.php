<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class File_versions_model extends SYSAD_Model 
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_file_version_list( array $columns, array $filter, array $order_arr, array $params )
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

			if( ISSET( $params['file_id'] ) )
			{
				$add_where 	.= " AND a.file_id = ? ";
				$extra_val[] = $params['file_id'];
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
				JOIN 	%s d
				ON 		a.file_id = d.file_id
				WHERE 	1 = 1
				$add_where
				$filter_str
				$order
				$limit
";

			$replacements 	= array(
				SYSAD_Model::CORE_TABLE_FILE_VERSIONS,
			   	SYSAD_Model::CORE_TABLE_USERS,
			   	SYSAD_Model::CORE_TABLE_USERS,
			   	SYSAD_Model::CORE_TABLE_FILE
			);

			$query 			= preg_replace_callback('/[\%]s/', function() use (&$replacements)
			{
				 return array_shift($replacements);
			}, $query);

			$val 		= array_merge( $val, $extra_val );

			$val 		= array_merge( $val, $filter_params );
			
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

	public function insert_file_versions( array $val )
	{
		$id 	= NULL;

		try
		{
			$id = $this->insert_data( SYSAD_Model::CORE_TABLE_FILE_VERSIONS, $val, TRUE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $id;
	}

	public function update_file_revisions( array $val, array $where )
	{
		try
		{
			$this->update_data( SYSAD_Model::CORE_TABLE_FILE_VERSIONS, $val, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function delete_file_revisions( array $where )
	{
		try
		{
			$this->delete_data( SYSAD_Model::CORE_TABLE_FILE_VERSIONS, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function get_latest_version( $id )
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query 	= "
				SELECT	a.version, a.file_version_id,
						a.display_name, a.file_type,
						a.file_extension, a.file_name, a.original_name, a.description
				FROM 	%s a
				WHERE 	a.file_id = ?
				ORDER 	BY a.file_version_id DESC
				LIMIT 	1
";
			$query 	= sprintf( $query, SYSAD_Model::CORE_TABLE_FILE_VERSIONS );

			$val[] 	= $id;

			$result = $this->query( $query, $val, TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;

	}

}