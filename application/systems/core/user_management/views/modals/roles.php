<?php 
$role_code = "";
$role_name = "";
$disabled = "";
$header = "Create a new role";
$default_system = "";
$maintainer_flag_check = "";
$default_role_sign_up_flag_check = "";

if(ISSET($role)){
	$role_code = (!EMPTY($role["role_code"]))? $role["role_code"] : "";
	$role_name = (!EMPTY($role["role_name"]))? $role["role_name"] : "";
	$default_system = ( ISSET($role["default_system"]) AND !EMPTY($role["role_name"]))? $role["default_system"] : "";
	$disabled = "disabled";
	$header = "Update role";

	$maintainer_flag_check = ( ISSET($role["maintainer_flag"]) AND !EMPTY($role["maintainer_flag"]))? "checked" : "";

	$default_role_sign_up_flag_check = ( ISSET($role["default_role_sign_up_flag"]) AND !EMPTY($role["default_role_sign_up_flag"]) AND $role['default_role_sign_up_flag'] == ENUM_YES)? "checked" : "";
}

$salt = gen_salt();
$token = in_salt($role_code, $salt);
?>	  

<input type="hidden" name="id" id="id_roles" value="<?php echo $role_code ?>">
<input type="hidden" name="salt" value="<?php echo $salt ?>">
<input type="hidden" name="token" value="<?php echo $token ?>">
<input type="hidden" id="system_json" value='<?php echo $system_json ?>'>
<div class="form-basic p-md p-t-lg">
  <div class="row">
	<div class="col s4">
	  <div class="input-field">
		<input type="text" class="white" name="role_code" placeholder="Enter Role Code" id="role_code" value="<?php echo $role_code ?>" <?php echo $disabled ?>/>
		<label for="role_code" class="required active">Code</label>
	  </div>
	</div>
	<div class="col s8">
	  <div class="input-field">
		<input type="text" class="white" placeholder="Enter Role" name="role_name" id="role_name" value="<?php echo $role_name ?>"/>
		<label for="role_name" class="required active">Role</label>
	  </div>
	</div>
  </div>
  <div class="row">
	<div class="col s12">
	  <div class="input-field">
		<label class="active required" for="system_role">Accessible System/s</label>
		<select name="system_role[]" required="" aria-required="true" id="system_role" class="" placeholder="Select System" multiple="multiple" >
		  <!-- <option value="">Select System</option> -->
		  <?php foreach($systems as $system): 
				$selected 		= ( !EMPTY( $sel_sys_roles ) AND in_array($system["system_code"], $sel_sys_roles) ) ? "selected" : "";
			?>
			<option value="<?php echo $system["system_code"] ?>" <?php echo $selected ?>><?php echo $system["system_name"] ?></option>				  
		  <?php endforeach; ?>
		</select>
	  </div>
	</div>
  </div>
  <div class="row">
	<div class="col s12">
	  <div class="input-field">
		<label class="active" for="default_system">Default System</label>
		<div class="help-text m-b-n-xs">System to be loaded upon login</div>
		<select name="default_system" id="default_system" class="selectize-roles" placeholder="Plese select system role first"  >
		  <option value="">Plese select system role first</option>
		  <?php 
		  	if( !EMPTY( $def_sys_opt ) ) :
		  ?>
			<?php 
				foreach( $def_sys_opt as $sys_opt ) :

					$sel_def 	= ( !EMPTY( $default_system ) AND $default_system == $sys_opt['system_code'] ) ? 'selected' : '';
			?>
			<option value="<?php echo $sys_opt['system_code'] ?>" <?php echo $sel_def ?> ><?php echo $sys_opt['system_name'] ?></option>
			<?php 
				endforeach;
			?>

		  <?php 
		  	endif;
		  ?>
		</select>
	  </div>
	</div>
  </div>
  <div class="row">
	<div class="col s7">
		<label class="active m-b-sm block" for="maintainer_flag">Allow users with this role to log in even in maintenance mode</label>
		<input type="checkbox" <?php echo $maintainer_flag_check ?> class="labelauty contact_flag" name="maintainer_flag" id="maintainer_flag" value="1" data-labelauty="Maintainer" />
	</div>
	<div class="col s4">
		<label class="active m-b-sm block" for="default_role_sign_up_flag">Default ROLE when signing-up</label>
		<input type="checkbox" <?php echo $default_role_sign_up_flag_check ?> class="labelauty contact_flag" name="default_role_sign_up_flag" id="default_role_sign_up_flag" value="1" data-labelauty="Default Sign Up Role" />
	</div>
  </div>
</div>