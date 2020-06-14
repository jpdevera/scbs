<?php 

   $ROOT_PATH 		= get_root_path();

	$avatar_path 	= $ROOT_PATH . PATH_USER_UPLOADS . $this->session->userdata('photo');
	$avatar_path 	= str_replace(array('\\','/'), array(DS,DS), $avatar_path);

	$avatar_photo 	= $this->session->userdata('photo');

	if( !is_dir( $avatar_path ) AND file_exists( $avatar_path ) )
	{	
		$avatar_src = base_url() . PATH_USER_UPLOADS . $this->session->userdata('photo');

		if( !EMPTY( $change_upload_path ) )
		{	
			$avatar_src = output_image($this->session->userdata('photo'), PATH_USER_UPLOADS);
		}

		$avatar_src = @getimagesize($avatar_path) ? $avatar_src : base_url() . PATH_IMAGES . "avatar.jpg";	
	}
	else
	{
		$avatar_photo = '';
	}

?>
<input type="hidden" id="user_upload_path" value="<?php echo PATH_USER_UPLOADS ?>"/>
<div class="row">
	<div class="col s3 grey lighten-4 valign-top">
		<div class="pinned section-panel">
			<div class="center-align m-t-md">
				<?php 
					if( !EMPTY( $avatar_photo ) ) :	
				?>	
				<div class="profile-avatar" style="background-image:url('<?php echo $avatar_src ?>')"></div>
				<?php 
					else :
				?>
				<img src="" class="profile_avatar photo-name" data-name="<?php echo $this->session->name ?>" alt="avatar">
				<?php 
					endif;
				?>
				<div id="profile_account_name" class="m-t-md"><?php echo $this->session->name ?></div>
				<div id="profile_username" class="m-t-sm">@<?php echo $this->session->username ?></div>
			</div>
			<div class="tabs-wrapper v-wrap">
				<div>
					<ul class="tabs m-t-lg">
						<li class="tab"><a id="link_tab_profile_account" class="active" href="#tab_profile_account" onclick="load_index('tab_profile_account', 'profile_account', '<?php echo CORE_USER_MANAGEMENT?>')"><i class="material-icons">person</i> Profile Information</a></li>
						<li class="tab"><a id="link_tab_profile_password" href="#tab_profile_password" onclick="load_index('tab_profile_password', 'profile_password', '<?php echo CORE_USER_MANAGEMENT?>')"><i class="material-icons">settings</i> Account Settings</a></li>
						<li class="tab"><a id="link_tab_profile_notifications" href="#tab_profile_notifications" onclick="load_index('tab_profile_notifications', 'index/core', 'notifications')"><i class="material-icons">notifications</i> Notifications</a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div class="col s9 valign-top">
		<div id="tab_profile_account" class="tab-content col s12"></div>
		<div id="tab_profile_password" class="tab-content col s12"></div>
		<div id="tab_profile_notifications" class="tab-content col s12"></div>
	</div>
</div>
