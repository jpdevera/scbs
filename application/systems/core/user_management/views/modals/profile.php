<?php 
$sp_status = get_sys_param_val(SYS_PARAM_STATUS, $user["status"]);

$id	= (!EMPTY($user["user_id"]))? $user["user_id"] : "";
$lname = (!EMPTY($user["lname"]))? $user["lname"] : "";
$fname = (!EMPTY($user["fname"]))? $user["fname"] : "";
$mname = (!EMPTY($user["mname"]))? $user["mname"] : "";
$nickname = (!EMPTY($user["nickname"]))? $user["nickname"] : "";
$female = ($user["gender"] == GENDER_FEMALE)? "checked" : "";
$male = ($user["gender"] == GENDER_MALE)? "checked" : "";
$email = (!EMPTY($user["email"]))? $user["email"] : "";
$job_title = (!EMPTY($user["job_title"]))? $user["job_title"] : "";
$contact_no = (!EMPTY($user["contact_no"]))? $user["contact_no"] : "";
$mobile_no = (!EMPTY($user["mobile_no"]))? $user["mobile_no"] : "";
//$username = (!EMPTY($user["username"]))? $user["username"] : "";
$photo = (!EMPTY($user["photo"]))? $user["photo"] : "";
$img_src = (!EMPTY($user["photo"]))? PATH_USER_UPLOADS . $user["photo"] : PATH_IMAGES . "avatar.jpg";

$photo_path = get_root_path() . $img_src;
$photo_path = str_replace(array('\\','/'), array(DS,DS), $photo_path);


$status = $sp_status["sys_param_value"];
$org_name = $user["org_name"];
$contact_flag = (ISSET($user["contact_flag"])) ? $user["contact_flag"] : "";

$pw_note = "Type in a new password below to reset / change current password.";

$salt = gen_salt();
$token	= in_salt($id, $salt);

?>
  <div class="table-display">
    <div class="table-cell valign-top profile-banner" style="width:40%">
	  <div class="avatar">
	    <div class="avatar-wrapper">
	      <?php 
	      	if( !EMPTY( $photo ) AND file_exists( $photo_path ) ) :
	      ?>
			<img id="profile_img" src="<?php echo base_url() . PATH_USER_UPLOADS . $this->session->photo ?>" />
	  	  <?php 
	  	    else :
	  	  ?>
	  		<img id="profile_img" class="profile_avatar" data-name="<?php echo $this->session->name ?>" />
		  <?php 
		  	endif;
		  ?>
		  <a href="#" id="profile_photo" class="m-r-sm">Edit Photo</a>
	    </div>
	  </div>
	  <div class="profile-detail center-align">
	    <h5><?php echo $this->session->name ?></h5>
	    <p class="center-align"><?php echo $job_title ?></p>
	  </div>
    </div>
    <div class="table-cell valign-top" style="width:60%">
	  <input type="hidden" name="user_id" value="<?php echo $id ?>">
	  <input type="hidden" name="salt" value="<?php echo $salt ?>">
	  <input type="hidden" name="token" value="<?php echo $token ?>">
	  <input type="hidden" name="status" value="<?php echo $status ?>">
	  <input type="hidden" name="contact_type" value="<?php echo $contact_flag ?>">
	  <input type="hidden" name="image" id="user_image" value="<?php echo $photo ?>"/>
	  <input type="hidden" id="user_upload_path" value="<?php echo PATH_USER_UPLOADS ?>"/>
	  
	  <div class="form-float-label">
		<div class="row m-n">
		  <div class="col s4">
			<div class="input-field">
			  <input type="text" data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" name="lname" id="lname" value="<?php echo $lname ?>"/>
			  <label for="lname">Last Name</label>
			</div>
		  </div>
		  <div class="col s5">
			<div class="input-field">
			  <input type="text" data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" name="fname" id="fname" value="<?php echo $fname ?>"/>
			  <label for="fname">First Name</label>
			</div>
		  </div>
		  <div class="col s3">
			<div class="input-field">
			  <input type="text" name="mname" id="mname" value="<?php echo $mname ?>"/>
			  <label for="mname">Middle Initial</label>
			</div>
		  </div>
		</div>
		
		<div class="row m-n">
		  <div class="col s4">
			<div class="input-field p-b-lg">
			  <input type="text" name="nickname" id="nickname" value="<?php echo $nickname ?>"/>
			  <label for="lname m-b-md">Nickname</label>
			</div>
		  </div>
		  <div class="col s8 p-b-xs">
			<div class="row m-b-n">
			  <div class="col s6 p-l-n">
				<label for="profile_gender_male" class="active m-b-sm block">Gender</label>
				<input type="radio" class="labelauty" name="gender" id="profile_gender_male" value="<?php echo MALE ?>" data-labelauty="Male" <?php echo $male ?>/>
			  </div>
			  <div class="col s6">
				<label class=" m-b-sm block">&nbsp;</label>
				<input type="radio" class="labelauty" name="gender" id="profile_gender_female" value="<?php echo FEMALE ?>" data-labelauty="Female" <?php echo $female ?>/>
			  </div>
			</div>
		  </div>   
		</div>
		
		<div class="row m-n">
		  <div class="col s6">
			<div class="input-field">
			  <input type="text" name="contact_no" id="contact_no" value="<?php echo $contact_no ?>"/>
			  <label for="contact_no">Telephone No.</label>
			</div>
		  </div>
		  <div class="col s6">
			<div class="input-field">
			  <input type="text" name="mobile_no" id="mobile_no" value="<?php echo $mobile_no ?>"/>
			  <label for="mobile_no">Mobile No.</label>
			</div>
		  </div>
		</div>
		
		<div class="row m-n">
		  <div class="col s7 disabled">
			<div class="input-field">
			  <label for="organization" class="active block">Department/Agency</label>
			  <input type="text" value="<?php echo $org_name?>" id="organization" disabled />
			</div>
		  </div>
		  <div class="col s5">
			<div class="input-field">
			  <input type="text" name="job_title" id="job_title" value="<?php echo $job_title ?>"/>
			  <label for="job_title">Job Title</label>
			</div>
		  </div>
		</div>
		
		<div class="row m-n">
		  <div class="col s12">
			<div class="input-field">
			  <input type="email" name="email" id="email" data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-type="email" data-parsley-trigger="keyup" value="<?php echo $email ?>"/>
			  <label for="email">Email Address</label>
			</div>
		  </div>
		</div>
		
		<div class="row m-n">
		  <div class="col s4">
			<div class="input-field">
			  <input type="password" name="current_password" id="current_password" <?php if(!ISSET($user)){ ?> class="validate" required="" aria-required="true" <?php } ?>/>
			  <label for="current_password">Current Password</label>
			</div>
		  </div>
		  <div class="col s4">
			<div class="input-field">
			  <input type="password" name="password" data-parsley-pass="true" disabled id="new_password" 
			  data-parsley-trigger="keyup" 
			  />
			  <label for="password">New Password</label>
			</div>
		  </div>
		  <div class="col s4">
			<div class="input-field">
			  <input type="password" name="confirm_password" disabled id="confirm_password" 
			  data-parsley-equalto="#new_password" 
			  data-parsley-trigger="keyup" 
			  />
			  <label for="confirm_password">Confirm Password</label>
			</div>
		  </div>
		</div>
	  </div>
    </div>
  </div>