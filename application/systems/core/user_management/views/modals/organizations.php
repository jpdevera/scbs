<?php
$id       = (ISSET($id)) ? $id : '';
$salt     = (ISSET($salt)) ? $salt : '';
$token    = (ISSET($token)) ? $token : '';
$org_code = (ISSET($org_details['org_code']) && ! EMPTY($org_details['org_code'])) ? $org_details['org_code'] : '';
$disabled = (ISSET($org_details['org_code']) && ! EMPTY($org_details['org_code'])) ? 'disabled' : '';
$org_name = (ISSET($org_details['name']) && ! EMPTY($org_details['name'])) ? $org_details['name'] : '';
$website  = (ISSET($org_details['website']) && ! EMPTY($org_details['website'])) ? $org_details['website'] : '';
$email    = (ISSET($org_details['email']) && ! EMPTY($org_details['email'])) ? $org_details['email'] : '';
$phone    = (ISSET($org_details['phone']) && ! EMPTY($org_details['phone'])) ? $org_details['phone'] : '';
$fax      = (ISSET($org_details['fax']) && ! EMPTY($org_details['fax'])) ? $org_details['fax'] : '';
$org_parent_code = (ISSET($org_details['org_parent']) && ! EMPTY($org_details['org_parent'])) ? $org_details['org_parent'] : '';
$org_short_name  = (ISSET($org_details['short_name']) && ! EMPTY($org_details['short_name'])) ? $org_details['short_name'] : '';
$organization_type_id  = (ISSET($org_details['organization_type_id']) && ! EMPTY($org_details['organization_type_id'])) ? $org_details['organization_type_id'] : '';

$org_logo 			= (ISSET($org_details['logo']) && ! EMPTY($org_details['logo'])) ? $org_details['logo'] : '';
$org_logo_orig_name  = (ISSET($org_details['logo_orig_name']) && ! EMPTY($org_details['logo_orig_name'])) ? $org_details['logo_orig_name'] : '';

$org_checked 		= "";
$org_stat_checked 	= "checked";

if( ISSET( $org_details['status'] ) )
{
	if( $org_details['status'] == ENUM_YES )
	{
		$org_stat_checked = "checked";
	}
	else
	{
		$org_stat_checked = "";	
	}
}
else
{
	
}

if( !EMPTY( $org_parents ) )
{
	$org_checked = "checked";
}

$checked_sys 	= '';

if( ISSET( $org_details['system_owner'] ) AND $org_details['system_owner'] == ENUM_YES )
{
	$checked_sys = 'checked';
}

?>

<input type="hidden" name="id" id="id_organizations" value="<?php echo $id; ?>"/>
<input type="hidden" name="salt"  id="salt" value="<?php echo $salt; ?>"/>
<input type="hidden" name="token" id="token" value="<?php echo $token; ?>"/>
<input type="hidden" id="disabled_inp" value="<?php echo $disabled_mod ?>">

<div class="form-basic p-md p-t-lg">

	<div class="row">

		<div class="col s6">
			<div class="input-field">
				<label for="organization_type" class="active">Organization Type</label>
				<select name="organization_type" id="organization_type" <?php echo $disabled_mod ?>class="selectize">
					<option value="">Please select</option>
					<?php
						if( !EMPTY( $org_types ) ) :
					?>
					<?php 
						foreach( $org_types as $ot_d ) :

							$ot_pa = base64_url_encode($ot_d['organization_type_id']);

							$sel_ot_org = ( !EMPTY( $organization_type_id ) AND $organization_type_id == $ot_d['organization_type_id'] ) ? 'selected' : '';
					?>
					<option <?php echo $sel_ot_org ?> value="<?php echo $ot_pa ?>"><?php echo $ot_d['name'] ?></option>
					<?php 
						endforeach;
					?>

					<?php 
						endif;
					?>
				</select>
				
			</div>
		</div>

		<div class="col s3">
			<div class="input-field">
				
				<input type="text" class="white" data-parsley-required="true" name="org_code"  id="org_code" value="<?php echo $org_code; ?>" <?php echo $disabled ?>  <?php echo $disabled_mod ?> />
				<label for="org_code" class="required active">Organization Code</label> 
			</div>
		</div>
		
	
	</div>

	<div class="row">
		<div class="col s4">
			<div class="input-field">
				<label for="org_short_name" class="required active">Organization Short Name</label>
				<input type="text" class="white" name="org_short_name"  <?php echo $disabled_mod ?> data-parsley-required="true" id="org_short_name" value="<?php echo $org_short_name; ?>" /> 
			</div>
		</div>
		<div class="col s5">
			<div class="input-field">
				<label for="org_name" class="required active">Organization Name</label>
				<input type="text" class="white" data-parsley-required="true" <?php echo $disabled_mod ?> name="org_name"  id="org_name" value="<?php echo $org_name; ?>"  /> 
			</div>
		</div>

		<div class="col s3">
			<div class="input-field">
				<input type="checkbox" class="labelauty" name="system_owner" value="" data-labelauty="No|Yes" id="system_owner" <?php echo $checked_sys ?> <?php echo $disabled_mod ?> />
				<label for="system_owner" class="active">System Owner ?</label>
			</div>
		</div>
	</div>

<!-- 	<div class="row m-n">
	<div class="col s12">
		<div class="input-field">
			<label for="parent_org_code" class="active">Parent Organization</label>
			<select name="parent_org_code" id="parent_org_code" class="selectize-orgs"  placeholder="Select sector">
					<option value=""></option>
					<?php //foreach($other_orgs as $key => $val): ?>
						<option value="<?php //echo $val['value']; ?>"><?php //echo $val['text']; ?></option>
					<?php //endforeach; ?>
			</select> 
		</div>
	</div>
</div>	 -->
	
	<div class="row">
		<div class="col s4">
			<div class="input-field">
				<label for="website" class="active">Website</label>
				<input type="text" class="white" name="website" <?php echo $disabled_mod ?>  placeholder="e.g. https://www.google.com" id="website" value="<?php echo $website; ?>"  /> 
			</div>
		</div>
		<div class="col s4">
			<div class="input-field">
				<label for="email" class="active">Email</label>
				<input type="email" <?php echo $disabled_mod ?> class="white" data-parsley-type="email" name="email" placeholder="e.g. jaundelacruz@gmail.com" id="email" value="<?php echo $email; ?>"  /> 
			</div>
		</div>
		
		<div class="col s4">
			<div class="input-field">
				<label for="tel_no" class="active">Telephone No.</label>
				<input type="text" <?php echo $disabled_mod ?> class="white" data-parsley-type="integer" name="tel_no"  id="tel_no" value="<?php echo $phone; ?>"  /> 
			</div>
		</div>
	</div>	
	<div class="row">
		<div class="col s6">
			<div class="input-field">
				<label for="fax_no" class="active">Parent Organizations</label>
				<select name="parent_organizations[]" <?php echo $disabled_mod ?> data-loadItemsUrl="<?php echo base_url().CORE_USER_MANAGEMENT.'/organizations/get_lazy_parent_orgs' ?>" data-extra_opt_function="Organizations.extra_opt_function(options);" multiple="multiple" class="lazy-selectize">
					<option value="">Please select</option>
					<?php
						if( !EMPTY( $parent_org_dr ) ) :
					?>
					<?php 
						foreach( $parent_org_dr as $p_d ) :

							$enc_pa = base64_url_encode($p_d['org_code']);

							$sel_p_org = ( !EMPTY( $sel_par_org ) AND in_array($p_d['org_code'], $sel_par_org) ) ? 'selected' : '';

							$p_d_json 	= json_encode($p_d);
					?>
					<option <?php echo $sel_p_org ?> data-data='<?php echo $p_d_json ?>' value="<?php echo $enc_pa ?>"><?php echo $p_d['name'] ?></option>
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
	<div class="row m-n">
		<div class="col s12">
			<div class="input-field m-b-md">
				<label for="logo_attachment_upload" class="active">Logo</label>
				<div class="field-multi-attachment p-t-md">
					<input type="hidden" <?php echo $disabled_mod ?> data-parsley-errors-container=".my_error_container_award" name="org_logo" id="org_logo" value="<?php echo $org_logo;?>" class="form_dynamic_upload" data-origfile="<?php echo $org_logo_orig_name;?>" >
					<input type="hidden" name="org_logo_orig_filename" id="org_logo_orig_filename" value="<?php echo $org_logo_orig_name;?>" class="form_dynamic_upload_origfilename">
					<a href="#" id="org_logo_upload" class="tooltipped m-r-sm" data-position="bottom" data-delay="50" data-tooltip="Upload">Upload</a>
				</div>
			</div>
		</div>
	</div>

	<div class="row m-n">
		<div class="col s3">
			<div class="input-field">
				<input type="checkbox" class="labelauty " name="status" value="1" data-labelauty="Inactive|Active" id="status_ch" <?php echo $disabled_mod ?>  <?php echo $org_stat_checked ?> />
				<label for="status_ch" class="active">Status</label>
			</div>
		</div>
	</div>

	<div class="row m-n white lighten-3 p-t-md par_div_tbl" style="display : none !important;">
		<div class="col s8 b-n">
			<h5 class="form-header">Org Parents</h5>
		</div>
		<div class="col s4 right-align">
			<button type="button" id="add_parent" class="btn btn-secondary">Add Parents</button>
		</div>
	</div>
	<!-- <div class="row m-n par_div_tbl" style="display : none !important;">
		<div class="col b-n">
			<table class="table table-default form-basic" id="tbl_org_parent" >
				<thead>
					<tr>
						<th width="35%">Group Type</th>
						<th width="45%">Organization</th>
						<th width="10%">&nbsp;</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>				
		</div>
	</div>	

	<div class="row m-n par_div_tbl" style="display : none !important;">
		<div class="col s12">
		&nbsp;
		</div>
	</div>		 -->
</div>