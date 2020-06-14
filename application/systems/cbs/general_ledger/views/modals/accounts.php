<?php

  /*echo "<pre>";
  print_r($accounts);
  die();*/
?>
<div class="form-basic p-md p-t-lg">
<input type="hidden" name="security" value="<?php echo $security ?>">
  <div class="row">
	<div class="col s6">
	  <div class="input-field">
		<input type="text" class="white" name="acct_code" placeholder="Enter Account Code" id="acct_code" value="<?=isset($accounts['acct_code']) ?$accounts['acct_code']:''?>" data-parsley-required="true" data-parsley-trigger="keyup" data-parsley-length="[10, 18]"/>
		<label for="acct_code" class="required active">Account Code</label>
	  </div>
	</div>
	<div class="col s6">
	  <div class="input-field">
		<input type="text" class="white" name="acct_name" placeholder="Enter Account Name" id="acct_name" value="<?=isset($accounts['acct_name']) ?$accounts['acct_name']:''?>" data-parsley-required="true" data-parsley-trigger="keyup" data-parsley-length="[3, 60]"/>
		<label for="acct_name" class="required active">Account Name</label>
	  </div>
	</div>
  </div>
  <div class="row">
  <div class="col s6">
    <div class="input-field">
    <select name="sort_id" required="" aria-required="true" id="sort_id" class="selectize" placeholder="Select GL Sort" data-parsley-validation-threshold="0" data-parsley-trigger-after-failure="change" data-default-value="<?=isset($accounts['sort_id']) ?$accounts['sort_id']:''?>">
      <option value="">Select GL Sort</option>
      <?php foreach($sorts as $k => $v): ?>    
        <option value="<?=$v['sort_id']?>"><?=$v['sort_name']?></option>
      <?php endforeach; ?>    
    </select>
    <label for="sort_id" class="required active">GL Sort</label>
    </div>
  </div>
 	<div class="col s6">
      <div class="input-field">
        <label for="active_flag" class="active required">Status</label>
        <div class="radio input m-t-sm">
           <input type="radio" class="labelauty md input_radio" name="active_flag" data-labelauty="Active" value="Y" <?=(isset($accounts['active_flag']) AND $accounts['active_flag']=='Y') ? 'checked' : ''?> />
           <input type="radio" class="labelauty md" name="active_flag" data-labelauty="Inactive" value="N" <?=(isset($accounts['active_flag']) AND $accounts['active_flag']=='N') ? 'checked' : ''?> />
        </div>
      </div>
    </div>
  </div>
</div>