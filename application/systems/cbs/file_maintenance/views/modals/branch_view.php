<?php
?>
<div class="form-basic p-md p-t-lg">
  <input type="hidden" name="security" value="<?php echo $security ?>">
  <div class="row">
    <div class="col s6">
      <div class="input-field">
        <input type="text" class="white" name="brn_code" placeholder="Enter Branch Code" id="brn_code" value="<?=isset($branch['brn_code']) ?$branch['brn_code']:''?>" data-parsley-required="true" data-parsley-trigger="keyup" disabled/>
        <label for="brn_code" class="active">Branch Code</label>
      </div>
    </div>
    <div class="col s6">
      <div class="input-field">
        <input type="text" class="white" name="brn_name" placeholder="Enter Branch Name" id="brn_name" value="<?=isset($branch['brn_name']) ?$branch['brn_name']:''?>" data-parsley-required="true" data-parsley-trigger="keyup" disabled/>
        <label for="brn_name" class="active">Branch Name</label>
      </div>
    </div>
  </div>
  	<div class="row">
	  <div class="col s12">
	      <div class="input-field">
	        <input type="text" class="white" name="institution_name" placeholder="Enter Institution Name" id="institution_name" value="<?=isset($branch['institution_name']) ?$branch['institution_name']:''?>" data-parsley-required="true" data-parsley-trigger="keyup" disabled/>
	        <label for="brn_name" class="active">Institution Name</label>
	      </div>
	  </div>  
	</div>
	<div class="row">
	  <div class="col s6">
	      <div class="input-field">
	        <input type="text" class="white datepicker" name="previous_system_date" placeholder="Enter Previous System Date" id="previous_system_date" value="<?=isset($branch['previous_system_date']) ?$branch['previous_system_date']:''?>" data-parsley-required="true" data-parsley-trigger="keyup" disabled/>
	        <label for="brn_name" class="active">Previous System Date</label>
	      </div>
	  </div> 
	    <div class="col s6">
	      <div class="input-field">
	        <input type="text" class="white datepicker" name="system_date" placeholder="Enter System Date" id="system_date" value="<?=isset($branch['system_date']) ?$branch['system_date']:''?>" data-parsley-required="true" data-parsley-trigger="keyup" disabled/>
	        <label for="brn_name" class="active">System Date</label>
	      </div>
	  </div>  
	</div>
	   <div class="row m-b-sm">
        <div class="col s12">
          <div class="input-field">
          	<label for="brn_name" class="active">Branch Logo</label>
            <div class="flat-upload-v2 dragdrop documents">
         
                <a href="javascript:;" id="attachment_document_upload"></a>

                <?php if (!empty($branch['sys_file_name'])): ?>
                  <?php //foreach ($supporting_documents as $file): ?>
                    <div class="ajax-file-upload-statusbar has-orig uploaded">
                      <div class="ajax-file-upload-filename"><?php print $branch['file_name']; ?></div>
                      <div class="actions">
                        <a target="_blank" href="<?php print base_url() .PATH_UPLOAD_BRANCH. $branch['sys_file_name']; ?>"
                        class="ajax-file-upload-green"><i class="material-icons">system_update_alt</i></a>
                        <a class="ajax-file-upload-red none"><i class="material-icons">delete</i></a>
                      </div>

                      <input type="hidden" name="org_filename" value="<?php print $branch['file_name'] ?>" />
                      <input type="hidden" name="sys_filename" value="<?php print $branch['sys_file_name'] ?>" />
                    </div>
                  <?php //endforeach; ?>
                <?php endif; ?>
              
            </div>
          </div>
        </div>
      </div>
</div>