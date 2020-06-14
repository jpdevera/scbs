<div class="form-basic panel m-md p-md">
  <form id="form_personal_information" class="form-vertical form-styled">
    <input type="hidden" name="security" value="<?php //print $security; ?>" />
    <input type="hidden" name="checker_citizenship_type" value="<?php //print $record['citizenship_type']; ?>" />

<div class="table-display">
      <div class="table-cell s3 valign-top">
        <div class="input-field label-right right-align m-r-sm">
          <label class="m-r-xs p-r-xxs required">Size of Firm</label>
        </div>
      </div>

      <div class="table-cell s9 valign-top">
        <div class="row">
          <div class="col s12">
            <div class="input-field">
              <input type="text" name="first_name" placeholder="First Name" data-parsley-required="true"
              value="<?php //print (!empty($record['first_name']) ? $record['first_name'] : ''); ?>" />
              <!-- <div class="font-thin m-t-xs">First Name</div> -->
            </div>
          </div>

        </div>
      </div>
    </div>

    <div class="table-display">
      <div class="table-cell s3 valign-top">
        <div class="input-field label-right right-align m-r-sm">
          <label class="m-r-xs p-r-xxs required">DOSRI</label>
        </div>
      </div>

      <div class="table-cell s9 valign-top">
        <div class="row">
          <div class="col s12">
            <div class="input-field">
              <input type="text" name="first_name" placeholder="First Name" data-parsley-required="true"
              value="<?php //print (!empty($record['first_name']) ? $record['first_name'] : ''); ?>" />
              <!-- <div class="font-thin m-t-xs">First Name</div> -->
            </div>
          </div>

        </div>
      </div>
    </div>

       <div class="table-display">
      <div class="table-cell s3 valign-top">
        <div class="input-field label-right right-align m-r-sm">
          <label class="m-r-xs p-r-xxs required">Risk Level</label>
        </div>
      </div>

      <div class="table-cell s9 valign-top">
        <div class="row">
          <div class="col s12">
            <div class="input-field">
              <input type="text" name="first_name" placeholder="First Name" data-parsley-required="true"
              value="<?php //print (!empty($record['first_name']) ? $record['first_name'] : ''); ?>" />
              <!-- <div class="font-thin m-t-xs">First Name</div> -->
            </div>
          </div>

        </div>
      </div>
    </div>

           <div class="table-display">
      <div class="table-cell s3 valign-top">
        <div class="input-field label-right right-align m-r-sm">
          <label class="m-r-xs p-r-xxs required">OTHERS</label>
        </div>
      </div>

      <div class="table-cell s9 valign-top">
        <div class="row">
          <div class="col s6">
            <div class="input-field">
              <input type="text" name="first_name" placeholder="First Name" data-parsley-required="true"
              value="<?php //print (!empty($record['first_name']) ? $record['first_name'] : ''); ?>" />
              <div class="font-thin m-t-xs">PEP</div>
            </div>
          </div>
          <div class="col s6">
            <div class="input-field">
              <input type="text" name="first_name" placeholder="First Name" data-parsley-required="true"
              value="<?php //print (!empty($record['first_name']) ? $record['first_name'] : ''); ?>" />
              <div class="font-thin m-t-xs">STAFF</div>
            </div>
          </div>

        </div>
      </div>
    </div>


</form>
</div>