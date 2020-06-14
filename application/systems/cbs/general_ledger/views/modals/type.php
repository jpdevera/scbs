<?php
?>
<div class="form-basic p-md p-t-lg">
<input type="hidden" name="security" value="<?php echo $security ?>">
  <div class="row">
	<div class="col s6">
	  <div class="input-field">
		<input type="text" class="white" name="type_code" placeholder="Enter Type Code" id="type_code" value="<?=isset($types['type_code']) ?$types['type_code']:''?>" data-parsley-required="true" data-parsley-trigger="keyup"/>
		<label for="type_code" class="required active">Type Code</label>
	  </div>
	</div>
	<div class="col s6">
	  <div class="input-field">
		<input type="text" class="white" name="type_name" placeholder="Enter Type Name" id="type_name" value="<?=isset($types['type_name']) ?$types['type_name']:''?>" data-parsley-required="true" data-parsley-trigger="keyup"/>
		<label for="type_name" class="required active">Type Name</label>
	  </div>
	</div>
  </div>
  <div class="row">
	<div class="col s6">
      <div class="input-field">
        <label for="position" class="active required">Position</label>
        <div class="radio input m-t-sm">
           <input type="radio" class="labelauty md input_radio" name="position" data-labelauty="Debit" value="D" <?=(isset($types['position']) AND $types['position']=='D') ? 'checked' : ''?> />
           <input type="radio" class="labelauty md" name="position" data-labelauty="Credit" value="C" <?=(isset($types['position']) AND $types['position']=='C') ? 'checked' : ''?>/>
        </div>
      </div>
    </div>
 	<div class="col s6">
      <div class="input-field">
        <label for="active_flag" class="active required">Status</label>
        <div class="radio input m-t-sm">
           <input type="radio" class="labelauty md input_radio" name="active_flag" data-labelauty="Active" value="Y" <?=(isset($types['active_flag']) AND $types['active_flag']=='Y') ? 'checked' : ''?> />
           <input type="radio" class="labelauty md" name="active_flag" data-labelauty="Inactive" value="N" <?=(isset($types['active_flag']) AND $types['active_flag']=='N') ? 'checked' : ''?> />
        </div>
      </div>
    </div>
  </div>
</div>