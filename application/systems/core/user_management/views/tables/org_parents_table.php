<?php 
	if( !EMPTY( $par_org_details ) ) :

		$del_url 		= '';

		foreach( $par_org_details as $details ) :

			$par_org 	= $details['org_parent'];
			$group_type = $details['group_type'];

			$par_org_enc 	= base64_url_encode( $details['org_parent'] );
			$group_enc 		= base64_url_encode( $details['group_type'] );

			$par_org_salt 	= gen_salt();
			$par_org_token 	= in_salt( $par_org, $par_org_salt );

			$group_salt 	= gen_salt();
			$group_token 	= in_salt( $group_type, $group_salt );

			$del_url 		= $org_code.'/'.$org_salt.'/'.$org_token.'/'.$par_org_enc.'/'.$par_org_salt.'/'.$par_org_token.'/'.$group_enc.'/'.$group_salt.'/'.$group_token;
?>
<tr>
	<td>
		<select name="org_group_type[]" class="selectize sel_org_group_tye">
			<option value="">Please select group type</option>
			<?php 
				if( !EMPTY( $org_group_types ) ) :

					foreach( $org_group_types as $org_group ) :

						$sel_grp 		= ( $group_type == $org_group['group_type'] ) ? 'selected' : '';

						$group_type_id 	= base64_url_encode( $org_group['group_type'] );
			?>
			<option value="<?php echo $group_type_id ?>" <?php echo $sel_grp ?>><?php echo $org_group['group_type_name'] ?></option>
			<?php 
					endforeach;
			?>

			<?php 
				endif;
			?>
		</select>
	</td>
	<td>
		<select name="org_parents[]" class="selectize sel_org_parent">
			<option value="">Please select parent</option>
			<?php 
				if( !EMPTY( $other_orgs ) ) :

					foreach( $other_orgs as $org_parent ) :

						$sel_par 		= ( $par_org == $org_parent['value'] ) ? 'selected' : '';

						$org_par_code 	= base64_url_encode( $org_parent['value'] );
			?>
			<option value="<?php echo $org_par_code ?>" <?php echo $sel_par ?>><?php echo $org_parent['text'] ?></option>
			<?php 
					endforeach;
			?>

			<?php 
				endif;
			?>
		</select>
	</td>
	<td class="valign-middle">
		<div class="table-actions center">
			<a href='javascript:;' class='delete tooltipped remove_org_parent' onclick="content_org_parent_delete('Parent', '<?php echo $del_url ?>' );" data-tooltip='Delete' data-position='bottom' data-delay='50'></a>
		</div>
	</td>
</tr>
<?php 
		endforeach;
?>

<?php 
	else :
?>

<tr>
	<td>
		<select name="org_group_type[]" class="selectize sel_org_group_tye">
			<option value="">Please select group type</option>
			<?php 
				if( !EMPTY( $org_group_types ) ) :

					foreach( $org_group_types as $org_group ) :

						$group_type_id 	= base64_url_encode( $org_group['group_type'] );
			?>
			<option value="<?php echo $group_type_id ?>"><?php echo $org_group['group_type_name'] ?></option>
			<?php 
					endforeach;
			?>

			<?php 
				endif;
			?>
		</select>
	</td>
	<td>
		<select name="org_parents[]" class="selectize sel_org_parent">
			<option value="">Please select parent</option>
			<?php 
				if( !EMPTY( $other_orgs ) ) :

					foreach( $other_orgs as $org_parent ) :

						$org_par_code 	= base64_url_encode( $org_parent['value'] );
			?>
			<option value="<?php echo $org_par_code ?>"><?php echo $org_parent['text'] ?></option>
			<?php 
					endforeach;
			?>

			<?php 
				endif;
			?>
		</select>
	</td>
	<td class="valign-middle">
		<div class="table-actions center">
			<a href='javascript:;' class='delete tooltipped remove_org_parent' data-tooltip='Delete' data-position='bottom' data-delay='50'></a>
		</div>
	</td>
</tr>
<?php 
	endif;
?>