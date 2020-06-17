<div class="form-basic panel m-md p-md">
  <div class="form-basic bg-white b-n p-t-sm p-md p-t-md">

      <div class="row m-t-sm m-b-n">
        <div class="col s12">
          <div class="panel-header b-b b-dashed b-light-gray p-b-xs m-b-sm">
            <p class="font-semibold m-b-xs">RELATIONSHIP</p>
            <div class="font-thin text-transform-none dark">
            List all relationship</div>
          </div>
        </div>
      </div>

      <div class="row m-b-lg">
        <div class="col s12">
          <table id="details_table" class="plain striped border add-row">
            <thead>
              <th width="40%">Relationship Type</th>
              <th width="50%">Name</th>
              <th width="10%"> <i id="add_details" class="material-icons tooltipped" data-tooltip="add row" data-position="left" data-delay="65">add_circle</i></th>
            </thead>
            <tbody>
                <tr>
                  <td width="40%" class="valign-top">
                    <div class="m-t-xs">
                    <select class="selectize" id="relationship_type_id" name="relationship_type_id[]" placeholder="Title"
                      data-parsley-required="false" data-parsley-trigger-after-failure="change"
                      data-default-value="<?=(isset($customer['relationship_type_id']) ? $customer['relationship_type_id'] : ''); ?>">
                        <option value="">Select Relationship Type</option>
                        <?php foreach ($relationship_types as $key => $value): ?>
                        <option value="<?=$value['relationship_type_id']?>"><?=$value['relationship_type_name']?></option>
                        <?php endforeach;?>
                      </select>
                        </div>
                  </td>

                  <td width="50%" class="valign-top">
                    <div class="input-field m-t-xs">
                     <input type="hidden" id="customer_id" name="customer_id[]"/>
                    <input type="text" id="full_name" name="full_name[]" placeholder="Name" data-parsley-required="false"
                    value="<?php //print $item['middle_name']; ?>" readonly/>
                    <span class="label-unit p-t-xs"><a id="modal_customer_id" href="#modal_customer" onclick="modal_customer_init('0', '', 'Search Customer')"
                      class="tooltipped" data-tooltip="Search Customer" data-position="top" data-delay="50">
                      <i class="material-icons">search</i></a></span>
                    </div>
                  </td>

                  <td width="10%" class="valign-middle">
                   <a href="javascript:;" id="delete_row" class="delete delete_row p-t-xs material-icons" data-tooltip="Delete"  data-position="left" data-delay="65">delete</a></i>
                  </td>
                </tr>
            </tbody>
          </table>
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

<style type="text/css">
.label-unit {
  height: 30px;
}
</style>