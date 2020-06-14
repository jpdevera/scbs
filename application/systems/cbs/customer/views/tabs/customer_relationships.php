<div class="form-basic panel m-md p-md">
  <div class="form-basic bg-white b-n p-t-sm p-md p-t-md">
    <form id="form_family_background" class="form-vertical form-basic">
      <input type="hidden" name="security" value="<?php //print $security; ?>">

      <div class="row m-t-sm m-b-n">
        <div class="col s12">
          <div class="panel-header b-b b-dashed b-light-gray p-b-xs">
            <p class="font-semibold m-b-n">FATHER'S NAME</p>
          </div>
        </div>
      </div>

      <div class="row m-b-lg">
        <div class="col s3">
          <div class="input-field">
            <input type="text" name="father_first_name" placeholder="First Name" data-parsley-required="true"
            value="<?php //print (!empty($records['father']['first_name']) ? $records['father']['first_name'] : '') ?>" />
            <div class="font-thin m-t-xs">First Name</div>
          </div>
        </div>

        <div class="col s2">
          <div class="input-field">
            <input type="text" name="father_middle_name" placeholder="Middle Name" data-parsley-required="false"
            value="<?php //print (!empty($records['father']['middle_name']) ? $records['father']['middle_name'] : '') ?>" />
            <div class="font-thin m-t-xs">Middle Name</div>
          </div>
        </div>

        <div class="col s3">
          <div class="input-field">
            <input type="text" name="father_last_name" placeholder="Last Name" data-parsley-required="true"
            value="<?php //print (!empty($records['father']['last_name']) ? $records['father']['last_name'] : '') ?>" />
            <div class="font-thin m-t-xs">Last Name</div>
          </div>
        </div>

        <div class="col s2">
          <div class="input-field">
            <input type="text" name="father_ext_name" placeholder="Ext. Name"
            value="<?php //print (!empty($records['father']['ext_name']) ? $records['father']['ext_name'] : '') ?>" />
            <div class="font-thin m-t-xs">Extension Name</div>
          </div>
        </div>

        <div class="radios">
          <input type="checkbox" name="father_deceased" class="labelauty" data-labelauty="deceased|deceased"
          <?php //print (!EMPTY($records['father']['deceased_flag']) AND $records['father']['deceased_flag'] === ENUM_YES ? 'checked' : ''); ?> />
        </div>
      </div>

      <div class="row m-t-sm m-b-n">
        <div class="col s12">
          <div class="panel-header b-b b-dashed b-light-gray p-b-xs">
            <p class="font-semibold m-b-xs">MOTHER'S NAME</p>
            <div class="font-thin text-transform-none dark">Name of your mother when she was single or before marriage</div>
          </div>
        </div>
      </div>

      <div class="row m-b-lg">
        <div class="col s3">
          <div class="input-field">
            <input type="text" name="mother_first_name" placeholder="First Name" data-parsley-required="true"
            value="<?php //print (!empty($records['mother']['first_name']) ? $records['mother']['first_name'] : '') ?>" />
            <div class="font-thin m-t-xs">First Name</div>
          </div>
        </div>

        <div class="col s2">
          <div class="input-field">
            <input type="text" name="mother_middle_name" placeholder="Middle Name" data-parsley-required="false"
            value="<?php //print (!empty($records['mother']['middle_name']) ? $records['mother']['middle_name'] : '') ?>" />
            <div class="font-thin m-t-xs">Middle Name</div>
          </div>
        </div>

        <div class="col s3">
          <div class="input-field">
            <input type="text" name="mother_last_name" placeholder="Last Name" data-parsley-required="true"
            value="<?php //print (!empty($records['mother']['last_name']) ? $records['mother']['last_name'] : '') ?>" />
            <div class="font-thin m-t-xs">Last Name</div>
          </div>
        </div>

        <div class="radios">
          <input type="checkbox" name="mother_deceased" class="labelauty" data-labelauty="deceased|deceased"
          <?php //print (!EMPTY($records['mother']['deceased_flag']) AND $records['mother']['deceased_flag'] === ENUM_YES ? 'checked' : ''); ?> />
        </div>
      </div>

      <div class="<?php //print $is_single; ?>">
        <div class="row m-t-sm m-b-n">
          <div class="col s12">
            <div class="panel-header b-b b-dashed b-light-gray p-b-xs">
              <p class="font-semibold">SPOUSE'S DETAILS</p>
            </div>
          </div>
        </div>

        <div class="row m-b-xs">
          <div class="col s3">
            <div class="input-field">
              <input type="text" name="spouse_first_name" placeholder="First Name" data-parsley-required="false"
              value="<?php //print (!empty($records['spouse']['first_name']) ? $records['spouse']['first_name'] : ''); ?>" />
              <div class="font-thin m-t-xs">First Name</div>
            </div>
          </div>

          <div class="col s2">
            <div class="input-field">
              <input type="text" name="spouse_middle_name" placeholder="Middle Name" data-parsley-required="false"
              value="<?php //print (!empty($records['spouse']['middle_name']) ? $records['spouse']['middle_name'] : ''); ?>" />
              <div class="font-thin m-t-xs">Middle Name</div>
            </div>
          </div>

          <div class="col s3">
            <div class="input-field">
              <input type="text" name="spouse_last_name" placeholder="Last Name" data-parsley-required="false"
              value="<?php //print (!empty($records['spouse']['last_name']) ? $records['spouse']['last_name'] : ''); ?>" />
              <div class="font-thin m-t-xs">Last Name</div>
            </div>
          </div>

          <div class="col s2">
            <div class="input-field">
              <input type="text" name="spouse_ext_name" placeholder="Ext. Name"
              value="<?php //print (!empty($records['spouse']['ext_name']) ? $records['spouse']['ext_name'] : ''); ?>" />
              <div class="font-thin m-t-xs">Extension Name</div>
            </div>
          </div>

          <div class="radios">
            <input type="checkbox" name="spouse_deceased" class="labelauty" data-labelauty="deceased|deceased"
            <?php //print (!EMPTY($records['spouse']['deceased_flag']) AND $records['spouse']['deceased_flag'] === ENUM_YES ? 'checked' : ''); ?> />
          </div>
        </div>

        <div class="row m-b-xs">
          <div class="col s5">
            <div class="input-field">
              <input type="text" name="spouse_occupation" placeholder="Occupation" data-parsley-required="false"
              value="<?php //print (!empty($records['spouse']['occupation']) ? $records['spouse']['occupation'] : ''); ?>" />
              <div class="font-thin p-t-xs">Occupation</div>
            </div>
          </div>

          <div class="col s5">
            <div class="input-field">
              <input type="text" name="spouse_company" placeholder="Employer / Business Name" data-parsley-required="false"
              value="<?php //print (!empty($records['spouse']['company']) ? $records['spouse']['company'] : ''); ?>" />
              <div class="font-thin p-t-xs">Employer / Business Name</div>
            </div>
          </div>
        </div>

        <div class="row m-b-lg">
          <div class="col s5">
            <div class="input-field">
              <input type="text" name="spouse_company_address" placeholder="Business Address" data-parsley-required="false"
              value="<?php //print (!empty($records['spouse']['company_address']) ? $records['spouse']['company_address'] : ''); ?>" />
              <div class="font-thin p-t-xs">Business Address</div>
            </div>
          </div>

          <div class="col s5">
            <div class="input-field">
              <input type="text" name="spouse_contact_nos" pattern="[0-9()/+-\s]{1,100}" placeholder="Telephone No." data-parsley-required="false"
              value="<?php //print (!empty($records['spouse']['contact_nos']) ? $records['spouse']['contact_nos'] : ''); ?>" />
              <div class="font-thin p-t-xs">Telephone No. ie: <span class="font-sm">(02)00-0000</span></div>
            </div>
          </div>
        </div>
      </div>

      <div class="row m-t-sm m-b-n">
        <div class="col s12">
          <div class="panel-header b-b b-dashed b-light-gray p-b-xs m-b-sm">
            <p class="font-semibold m-b-xs">DEPENDENTS</p>
            <div class="font-thin text-transform-none dark">
            List all and write their full name (first name and last name)</div>
          </div>
        </div>
      </div>

      <div class="row m-b-lg">
        <div class="col s12">
          <table id="children_table" class="plain striped border add-row">
            <thead>
              <th>first name</th>
              <th>middle name</th>
              <th>last name</th>
              <th>ext.</th>
              <th>birth date</th>
              <th></th>

              <th><i id="add_row" class="material-icons tooltipped center-align" data-tooltip="add row" data-position="left" data-delay="50">add_circle</i></th>
            </thead>
            <tbody>
              <?php //foreach ($records['childs'] as $item): ?>
                <tr>
                  <td width="20%" class="valign-top">
                    <input type="text" name="child_first_name[]" placeholder="First Name" data-parsley-required="false"
                    value="<?php //print $item['first_name']; ?>" />
                  </td>

                  <td width="15%" class="valign-top">
                    <input type="text" name="child_middle_name[]" placeholder="Middle Name" data-parsley-required="false"
                    value="<?php //print $item['middle_name']; ?>" />
                  </td>

                  <td width="20%" class="valign-top">
                    <input type="text" name="child_last_name[]" placeholder="Last Name" data-parsley-required="false"
                    value="<?php //print $item['last_name']; ?>" />
                  </td>

                  <td width="13%" class="valign-top">
                    <input type="text" name="child_ext_name[]" placeholder="Ext Name."
                    value="<?php //print $item['ext_name']; ?>" />
                  </td>

                  <td width="13%" class="valign-top">
                    <input type="text" class="datepicker "name="child_birth_date[]" placeholder="Birth Date"
                    data-parsley-required="false" value="<?php //print $item['birth_date']; ?>" />
                  </td>

                  <td>
                    <div class="radios"><input type="checkbox" class="labelauty" data-labelauty="deceased|deceased"
                    <?php //print ($item['deceased_flag'] === ENUM_YES ? 'checked' : ''); ?> />
                    <input type="hidden" name="child_deceased[]"
                    value="<?php //print ($item['deceased_flag'] === ENUM_YES ? 'checked' : ''); ?>" />
                    </div>
                  </td>

                  <td width="5%" class="valign-middle">
                    <a class="delete"><i class="material-icons tooltipped" data-tooltip="delete row" data-position="left"
                    data-delay="50" data-tooltip-id="7ceeb277-7e41-5065-1f37-74bd5ac4a6b4">remove_circle</i></a>
                  </td>
                </tr>
              <?php //endforeach; ?>

              <?php if (empty($records['childs'])): ?>
                <tr>
                  <td width="20%" class="valign-top">
                    <input type="text" name="child_first_name[]" placeholder="First Name"
                    data-parsley-required="false" />
                  </td>

                  <td width="15%" class="valign-top">
                    <input type="text" name="child_middle_name[]" placeholder="Middle Name"
                    data-parsley-required="false" />
                  </td>

                  <td width="20%" class="valign-top">
                    <input type="text" name="child_last_name[]" placeholder="Last Name"
                    data-parsley-required="false" />
                  </td>

                  <td width="13%" class="valign-top">
                    <input type="text" name="child_ext_name[]" placeholder="Ext. Name"
                    data-parsley-required="false" />
                  </td>

                  <td width="13%" class="valign-top">
                    <input type="text" class="datepicker "name="child_birth_date[]" placeholder="Birth Date"
                    data-parsley-required="false" />
                  </td>

                  <td>
                    <div class="radios"><input type="checkbox" class="labelauty" data-labelauty="deceased|deceased" />
                    <input type="hidden" name="child_deceased[]" /></div>
                  </td>

                  <td width="5%" class="valign-middle p-b-xs">
                    <a class="delete"><i class="material-icons tooltipped" data-tooltip="delete row" data-position="left"
                    data-delay="50" data-tooltip-id="7ceeb277-7e41-5065-1f37-74bd5ac4a6b4">remove_circle</i></a>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="row m-t-sm m-b-n">
        <div class="col s12">
          <div class="panel-header b-b b-dashed b-light-gray p-b-xs m-b-n">
            <p class="font-semibold m-b-xs">CONTACT PERSON</p>
            <div class="font-thin text-transform-none dark">In case of emergency, please notify</div>
          </div>
        </div>
      </div>

      <div class="row m-b-sm">
        <div class="col s3">
          <div class="input-field">
            <select class="selectize" name="emergency_relation" placeholder="Relation"
            data-parsley-trigger-after-failure="change"
            data-default-value="<?php //print (in_array($records['emergency']['other_relation_type'], $in_relation) ? $records['emergency']['other_relation_type'] : 'others'); ?>">
              <option></option>
              <option value="<?php //print RELATION_MOTHER; ?>">Mother</option>
              <option value="<?php //print RELATION_FATHER; ?>">Father</option>
              <?php if(EMPTY($is_single)): ?>
              <option value="<?php //print RELATION_SPOUSE; ?>">Spouse</option>
              <?php endif; ?>
              <option value="others">Others, specify</option>
            </select>
            <div class="font-thin m-t-xs">Relation</div>
          </div>
        </div>

        <div class="col s3">
          <div class="input-field">
            <input type="text" name="emergency_contact_no" pattern="[0-9()/+-\s]{1,100}" placeholder="Contact Number"
            value="<?php //print (!empty($records['emergency']['contact_nos']) ? $records['emergency']['contact_nos'] : ''); ?>" />
            <div class="font-thin m-t-xs">Contact Number <span class="font-sm">ie: (02)00-0000 or (0917)000-0000</span></div>
          </div>
        </div>

        <div class="col s4">
          <div class="input-field">
            <input type="text" name="emergency_address" placeholder="Address"
            value="<?php //print (!empty($records['emergency']['company_address']) ? $records['emergency']['company_address'] : ''); ?>" />
            <div class="font-thin m-t-xs">Address</div>
          </div>
        </div>
      </div>

      <div class="row m-b-lg relation-others
        <?php //print (in_array($records['emergency']['other_relation_type'], $in_relation) ? 'hide' : '') ?>">
        <div class="col s3">
          <div class="input-field">
            <input type="text" name="emergency_first_name" placeholder="First Name"
            value="<?php //print (!empty($records['emergency']['first_name']) ? $records['emergency']['first_name'] : '') ?>" />
            <div class="font-thin m-t-xs">First Name</div>
          </div>
        </div>

        <div class="col s3">
          <div class="input-field">
            <input type="text" name="emergency_last_name" placeholder="Last Name"
            value="<?php //print (!empty($records['emergency']['last_name']) ? $records['emergency']['last_name'] : '') ?>" />
            <div class="font-thin m-t-xs">Last Name</div>
          </div>
        </div>

        <div class="col s3">
          <div class="input-field">
            <input type="text" name="emergency_other_relation" placeholder="Relation"
            value="<?php //print (!empty($records['emergency']['other_relation_type']) ? $records['emergency']['other_relation_type'] : ''); ?>" />
            <div class="font-thin m-t-xs">Relation</div>
          </div>
        </div>
      </div>

      <div class="row m-l-sm m-r-sm m-b-n none">
        <div class="table-display form-button m-t-md p-t-md b-t b-dashed b-light-gray">
          <div class="table-cell s8 valign-middle"></div>
          <div class="table-cell s4 valign-middle right-align">
             <button type="submit" name="save_personal_info" id="save_personal_info" class="btn green lighten-1"
          data-btn-action="Saving">Save</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
