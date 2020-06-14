<?php 
$id						= "";
$roles_json 			= '';
$other_role_info_json 	= '';
$main_role_info_json 	= '';
$tab_cols				= 's4';
if(ISSET($user))
{
	$id			= (!EMPTY($user["user_id"]))? $user["user_id"] : "";
	$tab_cols	= 's6';
}

if( !EMPTY( $roles ) )
{
	$role_key 				= array_column( $roles, 'role_code' );
	$role_name 				= array_column( $roles, 'role_name' );
	
	$roles_json 			= json_encode( array_combine( $role_key, $role_name ) );
}

if( !EMPTY( $all_orgs ) )
{
	$org_key 				= array_column( $all_orgs, 'org_code' );
	$org_name 				= array_column( $all_orgs, 'name' );
	
	$org_json 			= json_encode( array_combine( $org_key, $org_name ) );
}

if( ISSET( $main_role ) AND !EMPTY( $main_role ) )
{
	$main_role_info_json 	= json_encode( $main_role );
}

if( ISSET( $other_roles ) AND !EMPTY( $other_roles ) )
{
	$other_role_info_json 	= json_encode( $other_roles );
}

$salt = gen_salt();
$token	= in_salt($id, $salt);


$data_to_tabs 		= array(
	'admnin_set_password'	=> $admnin_set_password,
	'dpa_enable' 			=> $dpa_enable,
	'has_agreement_text' 	=> $has_agreement_text,
	'strict_mode' 			=> $strict_mode,
	'confirm_dpa_message'	=> $confirm_dpa_message
);

?>
<div class="form-basic">
	<input type="hidden" id="disabled_str" value="<?php echo $disabled_str ?>">
	<input type="hidden" id="user_id_inp" name="user_id" id="user_id" value="<?php echo $id ?>">
	<input type="hidden" id="salt_inp" name="salt" value="<?php echo $salt ?>">
	<input type="hidden" id="token_inp" name="token" value="<?php echo $token ?>">
	
	<input type="hidden" id="role_json" value='<?php echo $roles_json ?>'/>
	<input type="hidden" id="org_json" value='<?php echo $org_json ?>'/>
	<input type="hidden" id="other_role_json" value='<?php echo $other_role_info_json ?>'/>
	<input type="hidden" id="main_role_json" value='<?php echo $main_role_info_json ?>'/>
	<input class="none" type="password" />

	<input type="hidden" id="dpa_enable_inp" value='<?php echo $dpa_enable ?>'/>
	<input type="hidden" id="has_agreement_text_inp" value='<?php echo $has_agreement_text ?>'/>
	<input type="hidden" id="confirm_dpa_message_inp" value="<?php echo $confirm_dpa_message ?>"/>
	<input type="hidden" id="confirm_dpa_message_body_inp" value='<?php echo $confirm_dpa_message_body ?>'/>
	
	
	<div class="tabs-wrapper full">
		<div>
			<ul class="tabs row">
				<li class="tab col <?php echo $tab_cols ?>"><a href="#tab_general_info">General Information</a></li>
				<li class="tab col <?php echo $tab_cols ?>"><a href="#tab_account_details">Account Details</a></li>
				<?php 
						
					if( 
						( !ISSET($user) AND $admnin_set_password )
						AND ( EMPTY( $dpa_enable ) 
							OR ( $has_agreement_text == DATA_PRIVACY_TYPE_BASIC ) 
							OR ( $has_agreement_text == DATA_PRIVACY_TYPE_STRICT AND $strict_mode != DATA_PRIVACY_STRICT_EMAIL_NOTIF )
						)
					)
					{ 

				?>
					<li class="tab col s4"><a href="#tab_welcome_email">Welcome Email</a></li>
				<?php 
					} 

				?>
			</ul>
		</div>
	</div>

	<div id="tab_general_info" class="tab-content col s12 p-md p-t-md"><?php $this->view('tabs/user_general_info'); ?></div>
	<div id="tab_account_details" class="tab-content col s12 p-md p-t-lg"><?php $this->view('tabs/user_account_details', $data_to_tabs); ?></div>
	<?php 
		if( ( !ISSET($user) 
			AND $admnin_set_password )
			AND ( EMPTY( $dpa_enable ) 
				OR ( $has_agreement_text == DATA_PRIVACY_TYPE_BASIC ) 
				OR ( $has_agreement_text == DATA_PRIVACY_TYPE_STRICT AND $strict_mode != DATA_PRIVACY_STRICT_EMAIL_NOTIF )
			)
		)
		{ 

	?>
		<div id="tab_welcome_email" class="tab-content col s12 p-md p-t-lg"><?php $this->view('tabs/user_welcome_email'); ?></div>
	<?php 

		} 

	?>
</div>