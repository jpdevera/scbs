<div class="form-basic p-md">
	<div class="panel white m-xs p-md m-b-n">
		<div class="section-note warning" data-label="Warning">
			To see the required excel format, download this
			<a href='<?php echo base_url().CORE_USER_MANAGEMENT ?>/Users/download_template' target="_blank">template</a>. <br /> <br />
			To import user list, upload an excel in .xls or .xlxs format:
		</div>
	</div>

	<div class="row">
		<div class="col s12">
			<div class="input-field">
				<div class="field-multi-attachment p-t-md">
					<input type="hidden" data-parsley-errors-container=".my_error_container_req_mov" data-parsley-error-message="This value is required." class='form_dynamic_upload statements_file_inp' name="org_import" id="org_import" value="" data-parsley-required="true" >
					<input type="hidden" name="org_import_orig_filename" id="org_import_orig_filename" value="" class="form_dynamic_upload_origfilename">

					<a href="#" id="org_import_upload" class="tooltipped m-r-sm" data-position="bottom" data-delay="50" data-tooltip="Upload File">Upload</a>
						<div class="my_error_container_req_mov"></div>
				</div>
			</div>
		</div>
	</div>
</div>