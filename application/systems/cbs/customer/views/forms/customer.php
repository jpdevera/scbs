<div class="page-title">
  <div class="table-display">
    <div class="table-cell valign-middle s6"><h5><?=$title?></h5></div>
  </div>
</div>

  <div class="row">

   <div class="col s3 p-l-n">
    <div class="form-basic panel m-md p-md">

  <div class="big-info bg-white p-sm b b-light-gray">
    <?php //if (!empty($record['full_name'])): ?>
      <div class="info b-r-n p-r-n p-n" style="height: 55px!important;">
        <div class="user-photo" style="width:90px;height:90px;top: -27px;left:-8px;">
          <div class="circle-container" style="width:90px;height:90px"/>
            <img src="<?php print 'http://localhost/ocbs/static/images/avatar/avatar_001.jpg'; ?>" >
          </div>
          <a id="user_photo_upload"></a>
        </div>
      </div>

      <div class="" style="float: right; margin-left: 10px;font-style: normal;font-weight: 600" >
        <div style="margin-bottom: 5px">
          <?php print 'Joseph De Vera'; ?><br>
        </div>
        <div><?php print 'Nov 14, 1993'; ?></div>
      </div>

    <p class="clear-both"></p>
  </div>
      <div class="tabs-wrapper v-wrap">
        <ul class="tabs m-t-sm customer">
          <li class="tab"><a id="customer_information" class="" href="#tab_customer_information" onclick="load_index('tab_customer_information', 'customer_information/index/<?=$customer_id?>', '<?php echo 'customer'?>')">
            <i class="material-icons">person</i> Profile Information</a></li>
          <li class="tab">
            <a id="customer_relationships" class="" href="#tab_customer_relationships" onclick="//load_index('tab_customer_relationships', 'customer_relationships/index/<?=$customer_id?>', '<?php //echo 'customer'?>')">
              <i class="material-icons">accessibility</i> Relationship
            </a>
          </li>
          <li class="tab">
            <a id="customer_codes" class="" href="#tab_customer_codes" onclick="//load_index('tab_customer_codes', 'customer_codes', 'customer')">
              <i class="material-icons">code</i> Customer Codes
            </a>
          </li>
          <li class="tab">
            <a id="customer_employment" class="" href="#tab_customer_employment" onclick="//load_index('tab_customer_employment', 'customer_employment', 'customer')">
              <i class="material-icons">folder_open</i> Employment
            </a>
          </li>
          <li class="tab">
            <a id="customer_business" class="" href="#tab_customer_business" onclick="//load_index('tab_customer_business', 'customer_business', 'customer')">
              <i class="material-icons">business_center</i> Business
            </a>
          </li>
          <li class="tab">
              <a id="customer_ids" class="" href="#tab_customer_ids" onclick="//load_index('tab_customer_ids', 'customer_ids', 'customer')">
                <i class="material-icons">account_box</i> Issued IDS
              </a>
          </li>
        </ul>
      </div>  
    </div>  
  </div> 

  <div class="col s9 p-l-n">
      <div id="tab_customer_information" class="tab-col"></div>
      <div id="tab_customer_relationships" class="tab-col"><?=$customer_relationships?></div>
      <div id="tab_customer_codes" class="tab-col"><?=$customer_codes?></div>
      <div id="tab_customer_employment" class="tab-col"><?=$customer_employment?></div>
      <div id="tab_customer_business" class="tab-col"><?=$customer_business?></div>
      <div id="tab_customer_ids" class="tab-col"><?=$customer_ids?></div>
  </div>

</div>


<style type="text/css">

.tabs{
  height: auto;
  white-space: normal;
}
.tab{
  display: block;
  width: 100%;
  border-bottom: 1px solid #f0f0f0;
  text-align: left;
}
.tab-col{
  width: 100%;
}
.indicator{
  display: none;
}

.label-right label {
  position: relative !important;
  vertical-align: top !important;
}
</style>
