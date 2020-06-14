<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Import_template_model extends SYSAD_Model 
{
	public function __construct()
	{	
		parent::__construct();
	}

	public function get_columns_table_import( $table, $dont_include = array(), $schema = DB_CORE, array $pass_fields = array(), $another_where = '', $another_where_val = array(), $pdo_type_fetch = PDO::FETCH_COLUMN )
	{
		$result 		= array();
		$val 			= array();

		$extra_val 		= array();
		$extra_where 	= '';

		try 
		{
			$fields 	= array('COLUMN_NAME');

			$def_dont 	= array(
				'created_by',
				'created_date',
				'modified_by',
				'modified_date'
			);

			if( !EMPTY( $pass_fields ) )
			{
				$fields = $pass_fields;
			}

			if( !EMPTY( $dont_include ) )
			{
				$def_dont = $dont_include;
			}

			$columns 	= str_replace(" , ", " ", implode(", ", $fields));

			if( !EMPTY( $def_dont ) )
			{
				$count_def					= count( $def_dont );

				$placeholder_def 			= str_repeat( '?,', $count_def );
				$placeholder_def 			= rtrim( $placeholder_def, ',' );
				
				$extra_where        	   .= " AND COLUMN_NAME NOT IN ( $placeholder_def ) ";

				$extra_val 					= array_merge( $extra_val, $def_dont );
			}

			$query 		= "
				SELECT 	$columns
				FROM 	information_schema.columns 
				WHERE 	table_schema 	= ?
				AND 	table_name 		= ?
				$extra_where
				$another_where
				
";

			$val[] 		= $schema;
			$val[]		= $table;

			if( !EMPTY( $extra_val ) )
			{
				$val 	= array_merge( $val, $extra_val );
			}
			
			if( !EMPTY( $another_where_val ) )
			{
				$val 	= array_merge( $val, $another_where_val );
			}
			
			$stmt 		= $this->query( $query, $val, FALSE );

			$result 	= $stmt->fetchAll($pdo_type_fetch);
		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $result;
	}

}
