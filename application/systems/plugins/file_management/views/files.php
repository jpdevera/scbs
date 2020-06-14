<input type="hidden" id="hid_<?php echo FILE_TYPE_DOCUMENTS ?>_val" value='<?php echo $document_json ?>'>
<input type="hidden" id="hid_<?php echo FILE_TYPE_IMAGES ?>_val" value='<?php echo $images_json ?>'>
<input type="hidden" id="hid_<?php echo FILE_TYPE_VIDEOS ?>_val" value='<?php echo $videos_json ?>'>
<input type="hidden" id="hid_<?php echo FILE_TYPE_AUDIOS ?>_val" value='<?php echo $audios_json ?>'>

<div class="page-title">
	<div class="table-display">
		<div class="table-cell valign-middle s2"><h5>Files</h5></div>
		<div class="table-cell valign-middle s6 center-align">

		</div>
		<div class="table-cell valign-middle right-align s4">
			<div class="inline p-l-xs">
				<?php 
					if( $add_per ) :
				?>
				<button class="btn waves-effect waves-light green upload_mulitple lighten-2 modal_upload_file_trigger" onclick="Files.upload_multi();" name="add_files" id="add_files_multi" type="button"><i class="material-icons">library_add</i>Upload Multiple</button>

				<button style="display :none !important;" data-target="modal_upload_file" class="btn waves-effect waves-light green upload_mulitple lighten-2 modal_upload_file_trigger" name="add_files" id="add_files_multi_hide" onclick="modal_upload_file_init('', this)" data-modal_post='<?php echo $document_json ?>' type="button"><i class="material-icons">library_add</i>Upload Multiple</button>

				<button data-target="modal_upload_file" class="btn waves-effect waves-light green lighten-2 modal_upload_file_trigger" name="add_files" id="add_files" onclick="modal_upload_file_init('', this)" data-modal_post='<?php echo $document_json ?>' type="button"><i class="material-icons">library_add</i>Create New</button>
				<?php 
					endif;
				?>
			</div>
		</div>
	</div>
</div>

<div class="tabs-wrapper full">
	<div>
    	<ul class="tabs row">
			<li class="tab col s3">
	  			<a data-post='<?php echo $document_json ?>' href="#tab_display_<?php echo FILE_TYPE_DOCUMENTS ?>" onclick="load_index_post('tab_display_<?php echo FILE_TYPE_DOCUMENTS ?>', 'Files/tabs/', '<?php echo CORE_FILE_MANAGEMENT?>', this, 'Files.create_button_modal(\'<?php echo FILE_TYPE_DOCUMENTS ?>\', obj)')">
  					<i class="material-icons">insert_drive_file</i>
	  				<span class="hide-on-med-and-down">Documents</span>
	  			</a>
	  		</li>
	  		<li class="tab col s3">
	  			<a href="#tab_display_<?php echo FILE_TYPE_IMAGES ?>" data-post='<?php echo $images_json ?>' onclick="load_index_post('tab_display_<?php echo FILE_TYPE_IMAGES ?>', 'Files/tabs/', '<?php echo CORE_FILE_MANAGEMENT?>', this, 'Files.create_button_modal(\'<?php echo FILE_TYPE_IMAGES ?>\', obj)')">
	  				<i class="material-icons">image</i>
	  				<span class="hide-on-med-and-down">Images</span>
	  			</a>
	  		</li>
	  		<li class="tab col s3">
	  			<a href="#tab_display_<?php echo FILE_TYPE_VIDEOS ?>" data-post='<?php echo $videos_json ?>' onclick="load_index_post('tab_display_<?php echo FILE_TYPE_VIDEOS ?>', 'Files/tabs/', '<?php echo CORE_FILE_MANAGEMENT?>', this, 'Files.create_button_modal(\'<?php echo FILE_TYPE_VIDEOS ?>\', obj)')">
	  				<i class="material-icons">ondemand_video</i>
	  				<span class="hide-on-med-and-down">Videos</span>
	  			</a>
	  		</li>
	  		<li class="tab col s3">
	  			<a href="#tab_display_<?php echo FILE_TYPE_AUDIOS ?>" data-post='<?php echo $audios_json ?>' onclick="load_index_post('tab_display_<?php echo FILE_TYPE_AUDIOS ?>', 'Files/tabs/', '<?php echo CORE_FILE_MANAGEMENT?>', this, 'Files.create_button_modal(\'<?php echo FILE_TYPE_AUDIOS ?>\', obj)')">
	  				<i class="material-icons">audiotrack</i>
	  				<span class="hide-on-med-and-down">Audios</span>
	  			</a>
	  		</li>
	  	</ul>
	</div>
</div>

<div id="tab_display_<?php echo FILE_TYPE_DOCUMENTS ?>" class="tab-content col s12"></div>
<div id="tab_display_<?php echo FILE_TYPE_IMAGES ?>" class="tab-content col s12"></div>
<div id="tab_display_<?php echo FILE_TYPE_VIDEOS ?>" class="tab-content col s12"></div>
<div id="tab_display_<?php echo FILE_TYPE_AUDIOS ?>" class="tab-content col s12"></div>