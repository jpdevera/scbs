<div class="form-basic panel m-md p-md">
  <div class="form-basic bg-white b-n p-t-sm p-md p-t-md">
    <form id="form_family_background" class="form-vertical form-basic">
<div class="row m-t-sm m-b-n">
        <div class="col s12">
          <div class="panel-header b-b b-dashed b-light-gray p-b-xs m-b-sm">
            <p class="font-semibold m-b-xs">Issued ID's</p>
            <div class="font-thin text-transform-none dark">
            List all available id's</div>
          </div>
        </div>
      </div>

      <div class="row m-b-lg">
        <div class="col s12">
          <table id="children_table" class="plain striped border add-row">
            <thead>
              <th>ID Type</th>
              <th>ID Number</th>
              <th>Expiration Date</th>
              <th><i id="add_row" class="material-icons tooltipped center-align" data-tooltip="add row" data-position="left" data-delay="50">add_circle</i></th>
            </thead>
            <tbody>
              <?php //foreach ($records['childs'] as $item): ?>
                <tr>
                  <td width="30%" class="valign-top">
                    <input type="text" name="id_type[]" placeholder="ID TYpe" data-parsley-required="false"
                    value="<?php //print $item['first_name']; ?>" />
                  </td>

                  <td width="30%" class="valign-top">
                    <input type="text" name="id_number[]" placeholder="ID Number" data-parsley-required="false"
                    value="<?php //print $item['middle_name']; ?>" />
                  </td>

                  <td width="30%" class="valign-top">
                    <input type="text" name="exp_date[]" placeholder="Expiration Date" data-parsley-required="false"
                    value="<?php //print $item['last_name']; ?>" />
                  </td>

                  <td width="5%" class="valign-middle">
                    <a class="delete"><i class="material-icons tooltipped" data-tooltip="delete row" data-position="left"
                    data-delay="50" data-tooltip-id="7ceeb277-7e41-5065-1f37-74bd5ac4a6b4">remove_circle</i></a>
                  </td>
                </tr>
              <?php //endforeach; ?>

           
            </tbody>
          </table>
        </div>
      </div>
    </form>
      </div>
      </div>
