<?php 
$id = $description = $file_version_id = "";
$col_budget_year = "s12";
$disabled = "disabled = disabled";

if(ISSET($files)){
	$id = $files['file_id'];
	$file_version_id = $files['file_version_id'];
	$description = $files['description'];
	$display_name = $files['display_name'];
	$col_budget_year = "s5";
	$disabled = "";
}

$salt = gen_salt();
$token = in_salt($id, $salt);
?>
<div class="table-display m-b-lg">
  <?php if(!ISSET($files)){ ?>
  <div class="table-cell valign-top p-md" style="width:65%;">
	<div class="scroll-pane field-multi-attachment" style="height:380px">
      <a href="#" id="file_upload" class="tooltipped m-r-sm" data-position="bottom" data-delay="50" data-tooltip="Upload">Choose a file to upload</a>
    </div>
  </div>
  <?php } ?>
  <div class="table-cell valign-top b-l" style="border-color:#e2e7e7!important; width:35%; position:relative;">
	<!-- <form id="upload_file_form"> -->
	  <input type="hidden" name="id_files" value="<?php echo $id ?>" />
	  <input type="hidden" name="file_version_id" value="<?php echo $file_version_id ?>" />
	  <input type="hidden" name="salt" value="<?php echo $salt ?>" />
	  <input type="hidden" name="token" value="<?php echo $token ?>" />
	  
	  <div class="form-float-label">
		<div class="row m-n b-l-n">
		  <?php if(ISSET($files)){ ?>
		  <div class="col s7">
			<div class="input-field">
			  <label for="file_display_name" class="active block">Display Name</label>
			  <input type="text" name="file_display_name" id="file_display_name" value="<?php echo $display_name ?>"/>
			</div>
		  </div>
		  <?php } ?>
		  <div class="col <?php echo $col_budget_year ?>">
			<div class="input-field">
			  <label for="file_budget_year" class="active block">Budget Year</label>
			  <?php echo create_years(date('Y')-2, date('Y'), 'file_budget_year', date('Y')); ?> 
			</div>
		  </div>
		</div>
		<div class="row m-n b-l-n">
		  <div class="col s12">
			<div class="input-field">
			  <label for="file_description">Description</label>
			  <textarea name="file_description" id="file_description" class="materialize-textarea" style="min-height:120px!important"><?php echo $description ?></textarea>
			</div>
		  </div>
		</div>
	  </div>
	  
	  <!-- <div class="md-footer default" <?php if(!ISSET($files)){ ?> style="position:absolute; bottom:0; width:100%;" <?php } ?> >
		<?php //if($this->permission->check_permission(MODULE_ROLE, ACTION_SAVE)):?>
		<button type="submit" class="btn waves-effect waves-light" id="save_upload_file" value="<?php echo BTN_SAVE ?>" <?php echo $disabled ?> ><?php echo BTN_SAVE ?></button>
		<?php //endif; ?>
		<a class="btn-flat p-r-n p-l-md" id="cancel_upload_file">Cancel</a>
	  </div> -->
    <!-- </form> -->
  </div>
</div>

<script type="text/javascript">
/*$(function(){	
  $("#cancel_upload_file").on("click", function(){
	modalObj.closeModal();
  });
  
  <?php if(ISSET($files)){ ?>
    $('.input-field label, .materialize-textarea').addClass('active');
  <?php } ?>
  
  $('#upload_file_form').parsley();
  $('#upload_file_form').submit(function(e) {
    e.preventDefault();
    
	if ( $(this).parsley().isValid() ) {
	  var data = $(this).serialize();
	  
	  button_loader('save_upload_file', 1);
	  $.post("<?php echo base_url() . PROJECT_CORE ?>/files/process/", data, function(result) {
		if(result.flag == 0){
		  notification_msg("<?php echo ERROR ?>", result.msg);
		  button_loader('save_upload_file', 0);
		} else {
		  notification_msg("<?php echo SUCCESS ?>", result.msg);
		  button_loader("save_upload_file",0);
		  
		  $("#modal_upload_file").removeClass("md-show");
		  window.location.reload();
		}
	  }, 'json');       
    }
  });
});*/
</script>