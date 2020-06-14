<?php
?>
<div class="form-basic p-md p-t-lg">
  <input type="hidden" name="security" value="<?php echo $security ?>">
  <div class="row">
    <div class="col s6">
      <div class="input-field">
        <input type="text" class="white" name="holiday_title" placeholder="Enter Title" id="holiday_title" value="<?=isset($holidays['holiday_title']) ?$holidays['holiday_title']:''?>" data-parsley-required="true" data-parsley-trigger="keyup"/>
        <label for="holiday_title" class="required active">Title</label>
      </div>
    </div>
    <div class="col s6">
      <div class="input-field">
        <input type="text" class="white datepicker" name="holiday_date" placeholder="Enter Date" id="holiday_date" value="<?=isset($holidays['holiday_date']) ?$holidays['holiday_date']:''?>" data-parsley-required="true" data-parsley-trigger="keyup"/>
        <label for="holiday_date" class="required active">Date</label>
      </div>
    </div>
  </div>
  <div class="row">
  <div class="col s12">
    <div class="input-field">
      <textarea name="holiday_desc" id="holiday_desc" class="materialize-textarea" placeholder="Special Instructions"><?= isset($holidays['holiday_desc']) ? $holidays['holiday_desc']:''; ?></textarea>
      <label for="address" class="active">Description</label>
    </div>
  </div>  
</div>
  <div class="row">
    <div class="col s6">
      <div class="input-field">
        <label for="recurring_flag" class="active required">Recurring</label>
        <div class="radio input m-t-sm">
          <input type="radio" class="labelauty md input_radio" name="recurring_flag" data-labelauty="Yes" value="Y" <?=(isset($holidays['recurring_flag']) AND $holidays['recurring_flag']=='Y') ? 'checked' : ''?> />
          <input type="radio" class="labelauty md" name="recurring_flag" data-labelauty="No" value="N" <?=(isset($holidays['recurring_flag']) AND $holidays['recurring_flag']=='N') ? 'checked' : ''?> />
        </div>
      </div>
    </div>
  </div>
</div>