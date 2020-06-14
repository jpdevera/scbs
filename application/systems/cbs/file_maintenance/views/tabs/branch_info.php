<?php
  $head_office_checked  = !isset($types['head_office'])?'checked':'';
  $default_flag_checked = !isset($types['default_flag'])?'checked':'';  
?>
<div class="form-basic p-md p-t-lg">

  <div class="row">
    <div class="col s6">
      <div class="input-field">
        <input type="text" class="white" name="brn_code" placeholder="Enter Branch Code" id="brn_code" value="<?=isset($branch['brn_code']) ?$branch['brn_code']:''?>" data-parsley-required="true" data-parsley-trigger="keyup"/>
        <label for="brn_code" class="required active">Branch Code</label>
      </div>
    </div>
    <div class="col s6">
      <div class="input-field">
        <input type="text" class="white" name="brn_name" placeholder="Enter Branch Name" id="brn_name" value="<?=isset($branch['brn_name']) ?$branch['brn_name']:''?>" data-parsley-required="true" data-parsley-trigger="keyup"/>
        <label for="brn_name" class="required active">Branch Name</label>
      </div>
    </div>
  </div>

  <div class="row">
	  <div class="col s12">
	      <div class="input-field">
	        <input type="text" class="white" name="institution_name" placeholder="Enter Institution Name" id="institution_name" value="<?=isset($branch['institution_name']) ?$branch['institution_name']:''?>" data-parsley-required="true" data-parsley-trigger="keyup"/>
	        <label for="brn_name" class="required active">Institution Name</label>
	      </div>
	  </div>  
	</div>

	<div class="row">
	  <div class="col s6">
	      <div class="input-field">
	        <input type="text" class="white datepicker" name="previous_system_date" placeholder="Enter Previous System Date" id="previous_system_date" value="<?=isset($branch['previous_system_date']) ?$branch['previous_system_date']:''?>" data-parsley-required="true" data-parsley-trigger="keyup"/>
	        <label for="brn_name" class="required active">Previous System Date</label>
	      </div>
	  </div> 
	    <div class="col s6">
	      <div class="input-field">
	        <input type="text" class="white datepicker" name="system_date" placeholder="Enter System Date" id="system_date" value="<?=isset($branch['system_date']) ?$branch['system_date']:''?>" data-parsley-required="true" data-parsley-trigger="keyup"/>
	        <label for="brn_name" class="required active">System Date</label>
	      </div>
	  </div>  
	</div>

  <div class="row">
      <div class="col s2">
      <div class="input-field">
        <label for="head_office" class="active required">Head Office?</label>
        <div class="radio input m-t-sm">
           <input type="radio" class="labelauty md input_radio" name="head_office" data-labelauty="Yes" value="Y" <?=(isset($types['head_office']) AND $types['head_office']=='Y') ? 'checked' : ''?> />
           <input type="radio" class="labelauty md" name="head_office" data-labelauty="No" value="N" <?=(isset($types['head_office']) AND $types['head_office']=='N') ? 'checked' : $head_office_checked?> />
        </div>
      </div>
    </div>
      <div class="col s2">
      <div class="input-field">
        <label for="default_flag" class="active required">Default Branch?</label>
        <div class="radio input m-t-sm">
           <input type="radio" class="labelauty md input_radio" name="default_flag" data-labelauty="Yes" value="Y" <?=(isset($types['default_flag']) AND $types['default_flag']=='Y') ? 'checked' : ''?> />
           <input type="radio" class="labelauty md" name="default_flag" data-labelauty="No" value="N" <?=(isset($types['default_flag']) AND $types['default_flag']=='N') ? 'checked' : $default_flag_checked?> />
        </div>
      </div>
    </div>
  </div>
    
	   <div class="row m-b-sm">
        <div class="col s12">
          <div class="input-field">
          	<label for="brn_name" class="required active">Branch Logo</label>
            <div class="flat-upload-v2 dragdrop documents">
              <div class="field-multi-attachment input-file-upload">
                <a href="javascript:;" id="attachment_document_upload"></a>

                <?php if (!empty($branch['sys_file_name'])): ?>
                  <?php //foreach ($supporting_documents as $file): ?>
                    <div class="ajax-file-upload-statusbar has-orig uploaded">
                      <div class="ajax-file-upload-filename"><?php print $branch['file_name']; ?></div>
                      <div class="actions">
                        <a target="_blank" href="<?php print base_url() .PATH_UPLOAD_BRANCH. $branch['sys_file_name']; ?>"
                        class="ajax-file-upload-green"><i class="material-icons">system_update_alt</i></a>
                        <a class="ajax-file-upload-red"><i class="material-icons">delete</i></a>
                      </div>
                    </div>
                  <?php //endforeach; ?>
                <?php endif; ?>
              </div>
              <input type="hidden" name="file_name" value="<?=isset($branch['file_name']) ? $branch['file_name']:'';?>" />
              <input type="hidden" name="sys_file_name" value="<?=isset($branch['sys_file_name']) ? $branch['sys_file_name']:'';?>" />
              <span class="field-guide">(Allowed extensions: <?php print str_replace(',', ', ', $allowed_types); ?>
              <span> - <?php print $max_file_size; ?>)</span></span>
            </div>
          </div>
        </div>
      </div>
</div>