<?php

 /* echo "<pre>";
  print_r($sorts);
  die();*/
?>
<div class="form-basic p-md p-t-lg">
<input type="hidden" name="security" value="<?php echo $security ?>">
  <div class="row">
	<div class="col s6">
	  <div class="input-field">
		<input type="text" class="white" name="sort_code" placeholder="Enter Sort Code" id="sort_code" value="<?=isset($sorts['sort_code']) ?$sorts['sort_code']:''?>" data-parsley-required="true" data-parsley-trigger="keyup" data-parsley-length="[10, 18]"/>
		<label for="sort_code" class="required active">Sort Code</label>
	  </div>
	</div>
	<div class="col s6">
	  <div class="input-field">
		<input type="text" class="white" name="sort_name" placeholder="Enter Sort Name" id="sort_name" value="<?=isset($sorts['sort_name']) ?$sorts['sort_name']:''?>" data-parsley-required="true" data-parsley-trigger="keyup" data-parsley-length="[3, 60]"/>
		<label for="sort_name" class="required active">Sort Name</label>
	  </div>
	</div>
  </div>
  <div class="row">
  <div class="col s6">
    <div class="input-field">
    <select name="type_id" required="" aria-required="true" id="type_id" class="selectize" placeholder="Select GL Type" data-parsley-validation-threshold="0" data-parsley-trigger-after-failure="change" data-default-value="<?=isset($sorts['type_id']) ?$sorts['type_id']:''?>">
      <option value="">Select GL Type</option>
      <?php foreach($types as $k => $v): ?>    
        <option value="<?=$v['type_id']?>"><?=$v['type_name']?></option>
      <?php endforeach; ?>    
    </select>
    <label for="type_id" class="required active">GL Type</label>
    </div>
  </div>
 	<div class="col s6">
      <div class="input-field">
        <label for="active_flag" class="active required">Status</label>
        <div class="radio input m-t-sm">
           <input type="radio" class="labelauty md input_radio" name="active_flag" data-labelauty="Active" value="Y" <?=(isset($sorts['active_flag']) AND $sorts['active_flag']=='Y') ? 'checked' : ''?> />
           <input type="radio" class="labelauty md" name="active_flag" data-labelauty="Inactive" value="N" <?=(isset($sorts['active_flag']) AND $sorts['active_flag']=='N') ? 'checked' : ''?> />
        </div>
      </div>
    </div>
  </div>
</div>