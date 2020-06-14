<html>
<head>
	<title>Audit Trail</title>
  	<style type="text/css"><?php echo $styles; ?></style>
  	<style>
  	.form-title{
  		color:#7030A0; 
  		margin-bottom: 7px !important;
  	}
  	</style>
</head>

<body>

	<table style="padding-top: 20px !important;" class="table-report-advanced">
		<thead>
			<tr class="row-header">
				<th width="20%">User</th>
				<th width="12%">Module</th>
				<th width="30%">Activity</th>
				<th width="15%">Date</th>
				<th width="15%">I.P</th>
			</tr>
		</thead>
		<tbody>
			<?php
				if( !EMPTY( $form_list ) ) :
					
					foreach( $form_list as $r ):

						$img_src 		= base_url().PATH_IMAGES . "avatar.jpg";

						$photo_path 	= "";

						if( !EMPTY( $arow['photo'] ) )
						{
							$root_path	= get_root_path();
							$photo_path = $root_path.PATH_USER_UPLOADS.$r['photo'];
							$photo_path = str_replace(array('\\','/'), array(DS,DS), $photo_path);

							if( file_exists( $photo_path ) )
							{
								$img_src = base_url() . PATH_USER_UPLOADS . $r['photo'];
							}
							else
							{
								$photo_path = "";
							}
						}

						if( !EMPTY( $photo_path ) )
						{
							$avatar 	= '<img class="avatar" width="20" height="20" src="'.$img_src.'" /> ';
						}
						else
						{
							$avatar 	= '<img class="avatar default-avatar" data-name="'.$r["fname"].'" class="demo" /> ';
						}

						if( ISSET( $r['activity_date'] ) AND !EMPTY( $r['activity_date'] ) )
						{
							$date 	= date_format( date_create( $r['activity_date'] ), 'm/d/Y H:i:s' );
						}
			?>
			<tr>
				<td>
					<?php echo $r["name"] ?>
				</td>
				<td>
					<?php echo $r['module_name'] ?>
					
				</td>
				<td>
					<?php echo $r['activity'] ?>
				</td>
				<td>
					<?php echo $date ?>
				</td>
				<td><?php echo $r['ip_address'] ?></td>
			</tr>
			<?php
					endforeach;
			?>
			<?php
				else :
			?>
			<tr>
				<td colspan="5" class="center-align">No record(s) Found.</td>
			</tr>
			<?php
				endif;
			?>
		</tbody>

	</table>
</body>
</html>
