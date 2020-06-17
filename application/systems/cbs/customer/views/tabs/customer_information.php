<div class="form-basic panel m-md p-md">
		<input type="hidden" name="checker_citizenship_type" value="<?php //print $record['citizenship_type']; ?>" />

		<div class="table-display">
			<div class="table-cell s2 valign-top">
				<div class="input-field label-right right-align m-r-sm">
					<label class="m-r-xs p-r-xxs required">Title</label>
				</div>
			</div>

			<div class="table-cell s10 valign-top">
				<div class="row">
					<div class="col s3">
						<div class="input-field">
							<select class="selectize" name="title_id" placeholder="Title"
							data-parsley-required="true" data-parsley-trigger-after-failure="change"
							data-default-value="<?=(isset($customer['title_id']) ? $customer['title_id'] : ''); ?>">
								<option value="">Select Title</option>
								<?php foreach ($titles as $key => $value): ?>
								<option value="<?=$value['title_id']?>"><?=$value['title_name']?></option>
								<?php endforeach;?>
							</select>
							<!-- <div class="font-thin m-t-xs">Title</div> -->
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="table-display">
			<div class="table-cell s2 valign-top">
				<div class="input-field label-right right-align m-r-sm">
					<label class="m-r-xs p-r-xxs required">Name</label>
				</div>
			</div>

			<div class="table-cell s10 valign-top">
				<div class="row">
					<div class="col s3">
						<div class="input-field">
							<input type="text" name="first_name" placeholder="First Name" data-parsley-required="true" 
							value="<?= isset($customer['first_name']) ? $customer['first_name'] : ''; ?>" />
							<div class="font-thin m-t-xs">First Name</div>
						</div>
					</div>

					<div class="col s3">
						<div class="input-field">
							<input type="text" name="middle_name" placeholder="Middle Name" data-parsley-required="false"
							value="<?= isset($customer['middle_name']) ? $customer['middle_name'] : ''; ?>" />
							<div class="font-thin m-t-xs">Middle Name</div>
						</div>
					</div>

					<div class="col s3">
						<div class="input-field">
							<input type="text" name="last_name" placeholder="Last Name" data-parsley-required="true"
							value="<?= isset($customer['last_name']) ? $customer['last_name'] : ''; ?>" />
							<div class="font-thin m-t-xs">Last Name</div>
						</div>
					</div>

					<div class="col s3 p-r-n">
						<div class="input-field">
							<input type="text" name="ext_name" placeholder="Extension Name"
							value="<?= isset($customer['ext_name']) ? $customer['ext_name'] : ''; ?>" />
							<div class="font-thin m-t-xs">Extension Name</div>

						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="table-display">
			<div class="table-cell s2 valign-top">
				<div class="input-field label-right right-align m-r-sm">
					<label class="m-r-xs p-r-xxs required">Birth Details</label>
				</div>
			</div>

			<div class="table-cell s10 valign-top">
				<div class="row">
					<div class="col s3">
						<div class="input-field">
							<input type="text" name="birth_date" class="datepicker" placeholder="Date of Birth"
							data-parsley-required="true"
							value="<?= isset($customer['birth_date']) ? $customer['birth_date'] : ''; ?>" />
							<div class="font-thin m-t-xs">Date of Birth</div>
						</div>
					</div>

					<div class="col s6">
						<div class="input-field">
							<input type="text" name="birth_place" placeholder="Place of Birth"
							data-parsley-required="true"
							value="<?= isset($customer['birth_place']) ? $customer['birth_place'] : ''; ?>" />
							<div class="font-thin m-t-xs">City, town, etc.</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="table-display">
			<div class="table-cell s2 valign-top">
				<div class="input-field label-right right-align m-r-sm">
					<label class="m-r-xs p-r-xxs required">Sex</label>
				</div>
			</div>

			<div class="table-cell s10 valign-top">
				<div class="row">
					<div class="col s5">
						<div class="input-field sex-watcher">
							<div class="radio"
							data-default-value="<?= isset($customer['sex_code']) ? $customer['sex_code'] : ''; ?>">
							<input type="radio" name="sex_code" id="male" class="labelauty" value="M" data-labelauty="Male" checked />
							<input type="radio" name="sex_code" id="female" class="labelauty" value="F" data-labelauty="Female"
							data-parsley-required="true" />
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="table-display">
		<div class="table-cell s2 valign-top">
			<div class="input-field label-right right-align m-r-sm">
				<label class="m-r-xs p-r-xxs required">Civil Status</label>
			</div>
		</div>

		<div class="table-cell s10 valign-top">
			<div class="row">
				<div class="col s3">
					<div class="input-field civil-watcher">
						<select class="selectize" name="civil_status_id" placeholder="Civil Status"
						data-parsley-required="true" data-parsley-trigger-after-failure="change"
						data-default-value="<?= (isset($customer['civil_status_id']) ? $customer['civil_status_id'] : ''); ?>">
							<option value="">Select Civil Status</option>
							<?php foreach ($civil_status as $key => $value): ?>
							<option value="<?=$value['civil_status_id']?>"><?=$value['civil_status_name']?></option>
							<?php endforeach;?>
						</select>
					</div>
				</div>

				<div class="col s6 maiden-name hide">
					<div class="input-field">
						<input type="text" name="maiden_name" placeholder="Maiden name"
						value="<?=(isset($customer['maiden_name']) ? $customer['maiden_name']:'')?>" />
						<div class="font-thin m-t-xs">Maiden Name</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="table-display">
		<div class="table-cell s2 valign-top">
			<div class="input-field label-right right-align m-r-sm">
				<label class="m-r-xs p-r-xxs">Religion</label>
			</div>
		</div>

		<div class="table-cell s10 valign-top">
			<div class="row">
				<div class="col s3">
					<div class="input-field civil-watcher">
						<select class="selectize" name="religion_id" placeholder="Religion"
						data-parsley-trigger-after-failure="change"
						data-default-value="<?(isset($customer['religion_id']) ? $customer['religion_id'] : ''); ?>">
							<option value="">Select Religion</option>
							<?php foreach ($religions as $key => $value): ?>
							<option value="<?=$value['religion_id']?>"><?=$value['religion_name']?></option>
							<?php endforeach;?>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="table-display">
		<div class="table-cell s2 valign-top">
			<div class="input-field label-right right-align m-r-sm">
				<label class="m-r-xs p-r-xxs required">Citizenship</label>
			</div>
		</div>

		<div class="table-cell s10 valign-top">
			<div class="row">
				<div class="col s4">
					<div class="input-field">
						<select class="selectize" name="citizenship_type" placeholder="Citizenship"
						data-parsley-required="true">
						<option></option>
						<option value="F" selected>Filipino</option>
					</select>
				</div>
			</div>

			<div class="col s4 p-n m-t-xs m-l-sm">
				<div class="input-field">
					<div class="checkbox">
						<p><input type="checkbox" class="filled-in" id="dual_citizenship" />
							<label for="dual_citizenship">Dual Citizenship</label></p>
						</div>
					</div>
				</div>
			</div>

			<div class="hide" id="dual_citizen_div" style="position: relative; top: -15px;">
				<div class="dual-citizen-div-indicator"></div>
				<div class="dual-citizen-div-opt row">
					<div class="font-thin">If holder of dual citizenship, please indicate the details below</div>
					<div class="radio default"
					data-default-value="<?=(isset($record['dual_citizen_type']) ? $record['dual_citizen_type'] : ''); ?>">
					<div class="col s3 p-n">
						<div class="input-field m-t-md">
							<input type="radio" name="dual_citizen_type" id="birth" value="B" />
							<label for="birth">by Birth</label>
						</div>
					</div>

					<div class="col s4 p-n">
						<div class="input-field m-t-md">
							<input type="radio" name="dual_citizen_type" id="natural" value="N" />
							<label for="natural">by Naturalization</label>
						</div>
					</div>
				</div>

				<div class="col s5">
					<div class="input-field">
						<select class="selectize" name="dual_country_id" placeholder="Country"
						data-default-value="<?=(isset($record['dual_country_id']) ? $record['dual_country_id'] : ''); ?>">
						<option></option>
						<?php foreach ($countries_opts as $opt): ?>
							<option value="<?php //print $opt['country_id']; ?>">
								<?php //print $opt['country_name']; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="table-display m-b-sm">
	<div class="table-cell s2 valign-top">
		<div class="input-field label-right right-align m-r-sm">
			<label class="m-r-xs p-r-xxs required">Statistics</label>
		</div>
	</div>

	<div class="table-cell s10 valign-top">
		<div class="row m-b-n">
			<div class="col s3">
				<div class="input-field">
					<input type="text" id="height" name="height" class="right-align with-unit" placeholder="Height" data-parsley-required="true"
					value="<?=(isset($customer['height']) ? $customer['height'] : ''); ?>" />
					<span class="label-unit">(m)</span>
					<div class="font-thin m-t-xs">Height</div>
				</div>
			</div>

			<div class="col s3">
				<div class="input-field">
					<input type="text" id="weight" name="weight" class="right-align with-unit" placeholder="Weight" data-parsley-required="true"
					value="<?=(isset($customer['weight']) ? $customer['weight'] : ''); ?>" />
					<span class="label-unit">(kg)</span>
					<div class="font-thin m-t-xs">Weight</div>
				</div>
			</div>

			<div class="col s3">
				<div class="input-field">
					<select class="selectize" name="blood_type_id" placeholder="Blood Type"
					data-parsley-required="true" data-parsley-trigger-after-failure="change"
					data-default-value="<?=(isset($customer['blood_type_id']) ? $customer['blood_type_id'] : ''); ?>">
						<option value="">Select Blood Type</option>
						<?php foreach ($blood_types as $key => $value): ?>
						<option value="<?=$value['blood_type_id']?>"><?=$value['blood_type_name']?></option>
						<?php endforeach;?>
					</select>
					<div class="font-thin m-t-xs">Blood Type</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="table-display address" id="residential_div">
	<div class="table-cell s2 valign-top">
		<div class="input-field label-right right-align m-r-sm">
			<label class="m-r-xs p-r-xxs required">Residential Address</label>
		</div>
	</div>

	<div class="table-cell s10 valign-top">
		<div class="row m-b-sm">
			<div class="col s3">
				<div class="input-field">
					<input type="text" name="res_house_no" placeholder="House / Block / Lot No."
					data-parsley-required="false"
					value="<?=(isset($residential['house_no']) ? $residential['house_no'] : ''); ?>" />
					<div class="font-thin m-t-xs">House / Block / Lot No.</div>
				</div>
			</div>


			<div class="col s3">
				<div class="input-field">
					<input type="text" name="res_street" placeholder="Street" data-parsley-required="false"
					value="<?=(isset($residential['street']) ? $residential['street'] : ''); ?>" />
					<div class="font-thin m-t-xs">Street</div>
				</div>
			</div>

			<div class="col s3">
				<div class="input-field">
					<input type="text" name="res_subdivision" placeholder="Subdivision / Village"
					data-parsley-required="false"
					value="<?=(isset($residential['subdivision']) ? $residential['subdivision'] : ''); ?>" />
					<div class="font-thin m-t-xs">Subdivision / Village</div>
				</div>
			</div>

			<div class="col s3">
				<div class="input-field">
					<select class="selectize region" name="res_region" placeholder="Region"
					data-parsley-required="true" data-parsley-trigger-after-failure="change"
					data-default-value="<?=(isset($residential['region_code']) ? $residential['region_code'] : ''); ?>">
						<option value="">Select Region</option>
						<?php foreach ($regions as $key => $value): ?>
						<option value="<?=$value['region_code']?>"><?=$value['region_name']?></option>
						<?php endforeach;?>
					</select>
					<div class="font-thin m-t-xs">Region</div>
				</div>
			</div>

		</div>

		<div class="row">

			<div class="col s3">
				<div class="input-field m-t-xs">
					<select class="selectize province" name="res_province" placeholder="Province"
					data-parsley-required="true" data-parsley-trigger-after-failure="change" disabled
					value="<?=(isset($residential['province_code']) ? $residential['province_code'] : ''); ?>">
					<option></option>
				</select>
				<span class="label-unit p-t-xs href_res hide"><a href="#modal_citymuni" onclick="modal_citymuni_init('residential', '', 'Search City/Municipality')"
					class="tooltipped" data-tooltip="Search city/municipality" data-position="top" data-delay="50">
					<i class="material-icons">search</i></a></span>
					<div class="font-thin m-t-xs">Province</div>
				</div>
			</div>

			<div class="col s3">
				<div class="input-field m-t-xs">
					<select class="selectize citymuni" name="res_citymuni" placeholder="City / Municipality"
					data-parsley-required="true" data-parsley-trigger-after-failure="change" disabled
					value="<?=(isset($residential['district_city']) ? $residential['district_city'] : ''); ?>">
					<option></option>
				</select>
				<div class="font-thin m-t-xs">City / Municipality</div>
			</div>
		</div>

		<div class="col s3 p-r-n">
			<div class="input-field m-t-xs">
				<select class="selectize barangay" name="res_barangay" placeholder="Barangay"
				data-parsley-required="true" data-parsley-trigger-after-failure="change" disabled
				value="<?=(isset($residential['barangay_code']) ? $residential['barangay_code'] : ''); ?>">
				<option></option>
			</select>
			<div class="font-thin m-t-xs">Barangay</div>
		</div>
	</div>

	<div class="col s3 p-r-n">
		<div class="input-field m-t-xs">
			<input type="text" name="res_zipcode" placeholder="ZIP Code"
			data-parsley-required="false"
			value="<?=(isset($residential['postal_number']) ? $residential['postal_number'] : ''); ?>" />
			<div class="font-thin m-t-xs">ZIP Code</div>
		</div>
	</div>

</div>
</div>
</div>

<div class="table-display address">
	<div class="table-cell s2 valign-top">
		<div class="input-field label-right right-align m-r-sm">
			<label class="m-r-xs p-r-xxs required">Permanent Address</label>
		</div>
	</div>

	<div class="table-cell s10 valign-top">
		<div class="row m-b-sm">
			<div class="col s12 p-n m-l-xs m-t-xxs">
				<div class="input-field m-l-xxs">
					<div class="checkbox">
						<p><input type="checkbox" name="same_as_residential" id="same_as_residential"
							class="filled-in <?php //print ((empty($permanent) AND !empty($record['first_name'])) ? 'chck' : ''); ?>" />
							<label for="same_as_residential">Same as Residential Address?<span class="option-span">
							(check if permanent address is same with the above address)</span></label></p>
						</div>
					</div>
				</div>
			</div>

			<div id="permanent_div">
				<div class="row m-b-sm p-b-xs">
					<div class="col s3">
						<div class="input-field m-t-xs">
							<input type="text" name="per_house_no" placeholder="House / Block / Lot No."
							data-parsley-required="false"
							value="<?=(isset($permanent['house_no']) ? $permanent['house_no'] : ''); ?>" />
							<div class="font-thin m-t-xs">House / Block / Lot No.</div>
						</div>
					</div>

					<div class="col s3">
						<div class="input-field m-t-xs">
							<input type="text" name="per_street" placeholder="Street"
							data-parsley-required="false"
							value="<?=(isset($permanent['street']) ? $permanent['street'] : ''); ?>" />
							<div class="font-thin m-t-xs">Street</div>
						</div>
					</div>

					<div class="col s3">
						<div class="input-field m-t-xs">
							<input type="text" name="per_subdivision" placeholder="Subdivision / Village"
							data-parsley-required="false"
							value="<?=(isset($permanent['subdivision']) ? $permanent['subdivision'] : ''); ?>" />
							<div class="font-thin m-t-xs">Subdivision / Village</div>
						</div>
					</div>

					<div class="col s3">

						<div class="input-field m-t-xs">
							<select class="selectize region" name="per_region" placeholder="Region"
							data-parsley-required="true"
							data-default-value="<?=(isset($permanent['region_code']) ? $permanent['region_code'] : ''); ?>">
								<option value="">Select Region</option>
								<?php foreach ($regions as $key => $value): ?>
								<option value="<?=$value['region_code']?>"><?=$value['region_name']?></option>
								<?php endforeach;?>
							</select>
							<div class="font-thin m-t-xs">Region</div>
						</div>
					</div>

				</div>

				<div class="row m-b-xs">
					<div class="col s3">
						<div class="input-field m-t-n">
							<select class="selectize province" name="per_province" placeholder="Province"
							data-parsley-required="true" disabled
							value="<?=(isset($permanent['province_code']) ? $permanent['province_code'] : ''); ?>">
							<option></option>
						</select>
						<div class="font-thin m-t-xs">Province</div>
						<span class="label-unit p-t-xs href_per hide"><a href="#modal_citymuni" onclick="modal_citymuni_init('permanent', '', 'Search City/Municipality')"
							class="tooltipped" data-tooltip="Search city/municipality" data-position="top" data-delay="50">
							<i class="material-icons">search</i></a></span>
						</div>
					</div>

					<div class="col s3">
						<div class="input-field m-t-n">
							<select class="selectize citymuni" name="per_citymuni" placeholder="City / Municipality"
							data-parsley-required="true" disabled
							value="<?=(isset($permanent['district_city']) ? $permanent['district_city'] : ''); ?>">
							<option></option>

						</select>
						<div class="font-thin m-t-xs">City / Municipality</div>
					</div>
				</div>

				<div class="col s3 p-r-n">
					<div class="input-field m-t-n">
						<select class="selectize barangay" name="per_barangay" placeholder="Barangay"
						data-parsley-required="true" disabled
						value="<?=(isset($permanent['barangay_code']) ? $permanent['barangay_code'] : ''); ?>">
						<option></option>
					</select>
					<div class="font-thin m-t-xs">Barangay</div>
				</div>
			</div>

			<div class="col s3 p-r-n">
				<div class="input-field m-t-n">
					<input type="text" name="per_zipcode" placeholder="ZIP Code"
					data-parsley-required="false"
					value="<?=(isset($permanent['postal_number']) ? $permanent['postal_number'] : ''); ?>" />
					<div class="font-thin m-t-xs">ZIP Code</div>
				</div>
			</div>

		</div>
	</div>
</div>
</div>

<div class="table-display m-t-xs">
	<div class="table-cell s2 valign-top">
		<div class="input-field label-right right-align m-r-sm">
			<label class="m-r-xs p-r-xxs required">Contact Nos.</label>
		</div>
	</div>

	<div class="table-cell s10 valign-top">
		<div class="row">
			<div class="col s6">
				<div class="input-field">
					<input type="text" name="tel_no" pattern="[0-9()/+-\s]{1,100}" placeholder="Telephone No." data-parsley-required="false"
					value="<?=(isset($contact['Telephone Number']) ? $contact['Telephone Number']['contact_value'] : ''); ?>"/>
					<div class="font-thin m-t-xs">Telephone No. <span class="font-sm">ie : (02)00-0000</span></div>
				</div>
			</div>

			<div class="col s6 p-r-n">
				<div class="input-field">
					<input type="text" name="mobile_no" pattern="[0-9()/+-\s]{1,100}" placeholder="Mobile No." data-parsley-required="true"
					value="<?=(isset($contact['Cellphone Number']) ? $contact['Cellphone Number']['contact_value'] : ''); ?>" />
					<div class="font-thin m-t-xs">Mobile No. <span class="font-sm">ie : (0917)000-0000</span></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="table-display m-t-xs">
	<div class="table-cell s2 valign-top">
		<div class="input-field label-right right-align m-r-sm">
			<label class="m-r-xs p-r-xxs required">Email Address</label>
		</div>
	</div>

	<div class="table-cell s10 valign-top">
		<div class="row">
			<div class="col s6">
				<div class="input-field">
					<input type="text" name="email" placeholder="Email Address" data-parsley-required="true"
					value="<?=(isset($contact['Primary Email Address']) ? $contact['Primary Email Address']['contact_value'] : ''); ?>" />
					<div class="font-thin m-t-xs">Primary</div>
				</div>
			</div>

			<div class="col s6 p-r-n">
				<div class="input-field">
					<input type="text" name="alternate" placeholder="Alternate"
					value="<?=(isset($contact['Alternate Email Address']) ? $contact['Alternate Email Address']['contact_value'] : ''); ?>" />
					<div class="font-thin m-t-xs">Alternate</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="table-display form-button m-t-md p-t-md b-t b-dashed b-light-gray">
	<div class="table-cell s8 valign-middle"></div>
	<div class="table-cell s4 valign-middle right-align">
		<button name="btn_customer_information" id="btn_customer_information" class="btn lighten-1"
		data-btn-action="Save">Next</button>
	</div>
</div>
</form>
</div>
