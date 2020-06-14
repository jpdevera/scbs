<?php
/*print_r($holidays);
die();*/
$frp_option_checked  = !isset($setup['frp_option'])?'checked':'';
?>
<div class="form-basic p-md p-t-lg">
	<div class="row">
		<div class="col s6">
			<div class="input-field">
				<input type="text" class="white" name="passbook_length" placeholder="Enter Passbook Length" id="passbook_length" value="<?=isset($setup['passbook_length']) ?$setup['passbook_length']:''?>" data-parsley-required="true" data-parsley-trigger="keyup" data-parsley-type="number"/>
				<label for="passbook_length" class="required active">Passbook Length</label>
			</div>
		</div>
		<div class="col s6">
			<div class="input-field">
				<input type="text" class="white" name="passbook_row_length" placeholder="Enter Passbook Row Length" id="passbook_row_length" value="<?=isset($setup['passbook_row_length']) ?$setup['passbook_row_length']:''?>" data-parsley-required="true" data-parsley-trigger="keyup" data-parsley-type="number"/>
				<label for="passbook_row_length" class="required active">Passbook Row Length</label>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col s6">
			<div class="input-field">
				<input type="text" class="white" name="check_length" placeholder="Enter Check Length" id="check_length" value="<?=isset($setup['check_length']) ?$setup['check_length']:''?>" data-parsley-required="true" data-parsley-trigger="keyup" data-parsley-type="number"/>
				<label for="passbook_row_length" class="required active">Check Length</label>
			</div>
		</div>
		<div class="col s6">
			<div class="input-field">
				<input type="text" class="white" name="no_of_booklet" placeholder="Enter No. of Booklet" id="no_of_booklet" value="<?=isset($setup['no_of_booklet']) ?$setup['no_of_booklet']:''?>" data-parsley-required="true" data-parsley-trigger="keyup" data-parsley-type="number"/>
				<label for="no_of_booklet" class="required active">No. of Booklet</label>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col s6">
			<div class="input-field">
				<input type="text" class="white" name="no_check_per_booklet" placeholder="Enter No. of Check Per Booklet" id="no_check_per_booklet" value="<?=isset($setup['no_check_per_booklet']) ?$setup['no_check_per_booklet']:''?>" data-parsley-required="true" data-parsley-trigger="keyup" data-parsley-type="number"/>
				<label for="no_check_per_booklet" class="required active">No of Check Per Booklet</label>
			</div>
		</div> 
		<div class="col s6">
			<div class="input-field">
				<input type="text" class="white" name="transaction_serial" placeholder="Enter Transaction Serial" id="transaction_serial" value="<?=isset($setup['transaction_serial']) ?$setup['transaction_serial']:''?>" data-parsley-required="true" data-parsley-trigger="keyup" data-parsley-type="number"/>
				<label for="transaction_serial" class="required active">Transaction Serial</label>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col s6">
			<div class="input-field">
				<input type="text" class="white" name="bsp_code" placeholder="Enter BSP Code" id="bsp_code" value="<?=isset($setup['bsp_code']) ?$setup['bsp_code']:''?>" data-parsley-required="true" data-parsley-trigger="keyup" data-parsley-type="number"/>
				<label for="bsp_code" class="required active">BSP Code</label>
			</div>
		</div> 
		<div class="col s2">
			<div class="input-field">
				<label for="frp_option" class="active required">FRP?</label>
				<div class="radio input m-t-sm">
					<input type="radio" class="labelauty md input_radio" name="frp_option" data-labelauty="Yes" value="Y" <?=(isset($setup['frp_option']) AND $setup['frp_option']=='Y') ? 'checked' : ''?> />
					<input type="radio" class="labelauty md" name="frp_option" data-labelauty="No" value="N" <?=(isset($setup['frp_option']) AND $setup['frp_option']=='N') ? 'checked' : $frp_option_checked?> />
				</div>
			</div>
		</div>
		<div class="col s4">
			<div class="input-field">
				<input type="text" class="white" name="frp_path" placeholder="Enter FRP Path" id="frp_path" value="<?=isset($setup['frp_path']) ?$setup['frp_path']:''?>" data-parsley-required="false" data-parsley-trigger="keyup" data-parsley-type="number"/>
				<label for="frp_path" class="active">FRP path</label>
			</div>
		</div> 
	</div>

	<div class="row">
		<div class="col s6">
			<div class="input-field">
				<input type="text" class="white" name="backup_path" placeholder="Enter Backup Path" id="backup_path" value="<?=isset($setup['backup_path']) ?$setup['backup_path']:''?>" data-parsley-required="false" data-parsley-trigger="keyup" data-parsley-type="number"/>
				<label for="backup_path" class="active">Backup path</label>
			</div>
		</div>
		<div class="col s6">
			<div class="input-field">
				<input type="text" class="white" name="database_name" placeholder="Enter Database Name" id="database_name" value="<?=isset($setup['database_name']) ?$setup['database_name']:''?>" data-parsley-required="false" data-parsley-trigger="keyup" data-parsley-type="number"/>
				<label for="database_name" class="active">Database Name</label>
			</div>
		</div> 
	</div>

	<div class="row">
		<div class="col s12">
			<div class="input-field">
				<select name="holiday_id[]" data-parsley-required="true" aria-required="true" id="holiday_id" class="selectize" placeholder="Select Holidays" data-parsley-validation-threshold="0" data-parsley-trigger-after-failure="change" multiple>
					<option value="">Select Holidays</option>
					<?php foreach($holidays as $k => $v): ?> 
					<?php 
						$selected=""; 
						if( isset($holiday_id) AND in_array($v['holiday_id'], $holiday_id) ) $selected = 'selected'; 
					?>     
						<option value="<?=$v['holiday_id']?>" <?=$selected?> > <?=$v['holiday_title']?></option>
					<?php endforeach; ?>    
				</select>
				<label for="holiday_id" class="required active">Holidays</label>
			</div>
		</div>
	</div>
</div>

<div class="form-buttons m-t-lg">
	<div class="b-t b-dashed b-light-gray p-t-sm">
		<div class="right-align p-side">
			<button class="btn btn-success" data-btn-action="<?php echo BTN_SAVING ?>" name="submit_branch" id="submit_branch" type="button">Submit
			</button>
		</div>
	</div>
</div>