<?php 

	$disable 	= '';

	if( $action == ACTION_VIEW )
	{
		$disable = 'disabled';
	}

	if( !EMPTY( $details ) ) :
?>
	<?php 
		foreach( $details as $key => $r ) :

			$check_admin 	= "";

			$hid_admin_val 	= "";

			$hid_admin_val  = ( ISSET( $r['admin_flag'] ) AND !EMPTY( $r['admin_flag'] ) ) ? $r['admin_flag'] : "";

			$check_admin  	= ( ISSET( $r['admin_flag'] ) AND !EMPTY( $r['admin_flag'] ) ) ? 'checked' : "";
	?>
	<tr>
		<td>
			<div class="input-field m-n">
				<div>
					<select <?php echo $disable ?> class="selectize column-1" name="group_users[]" placeholder="Select user" data-parsley-group="column-1" data-parsley-unique=".column-1" data-parsley-trigger="change">
						<option value="">Please select</option>
						<?php 
							if( !EMPTY( $users ) ) :
						?>
							<?php 
								foreach( $users as $user ) :

									$sel_u 	 = ( $r['user_id'] == $user['user_id'] ) ? 'selected' : '';

									$id_user = base64_url_encode( $user['user_id'] );
							?>
							<option <?php echo $sel_u ?> value="<?php echo $id_user ?>" >
								<?php echo $user['fullname'] ?>
							</option>
							<?php 
								endforeach;
							?>

						<?php 
							endif;
						?>
					</select>
				</div>
				<div class="sel-group-error"></div>
			</div>			
		</td>
		<td>
			<div class="input-field m-n">
				<label class="label hide active" for="checkbox_members_<?php echo $key ?>"></label>
				<input type="hidden" class="checkbox_hidden" name="group_admins[]" value="<?php echo $hid_admin_val ?>" />
				<input type="checkbox" <?php echo $disable ?> <?php echo $check_admin ?> class="labelauty checkbox_members" id="checkbox_members_<?php echo $key ?>" name="group_admin_checks[]" data-labelauty="Set as Admin|Set as Admin" />
			</div>
		</td>
		<td class="valign-middle">
			<div class="table-actions center">
				<?php 
					if( EMPTY( $disable ) )  :
				?>
				<a href='javascript:;' class='delete tooltipped remove_group' data-tooltip='Delete' data-position='bottom' data-delay='50'></a>
				<?php 
					endif;
				?>
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
		<div class="input-field m-n">
			<div>
				<select <?php echo $disable ?> class="selectize column-1" name="group_users[]" placeholder="Select user" data-parsley-group="column-1" data-parsley-unique=".column-1" data-parsley-trigger="change">
					<option value="">Please select</option>
					<?php 
						if( !EMPTY( $users ) ) :
					?>
						<?php 
							foreach( $users as $user ) :

								$id_user = base64_url_encode( $user['user_id'] );
						?>
						<option value="<?php echo $id_user ?>" >
							<?php echo $user['fullname'] ?>
						</option>
						<?php 
							endforeach;
						?>

					<?php 
						endif;
					?>
				</select>
			</div>
			<div class="sel-group-error"></div>
		</div>			
	</td>
	<td class="center-align">
		<div class="input-field m-n">
			<label class="label hide active" for="checkbox_members"></label>
			<input type="hidden" class="checkbox_hidden" name="group_admins[]" value="" />
			<input <?php echo $disable ?> type="checkbox" class="labelauty checkbox_members" id="checkbox_members" name="group_admin_checks[]" data-labelauty="Set as Admin|Set as Admin" />
		</div>
	</td>
	<td class="valign-middle">
		<div class="table-actions center">
			<?php 
				if( EMPTY( $disable ) )  :
			?>
			<a href='javascript:;' class='delete tooltipped remove_group' data-tooltip='Delete' data-position='bottom' data-delay='50'></a>
			<?php 
				endif;
			?>
		</div>
	</td>
</tr>
<?php 
	endif;
?>