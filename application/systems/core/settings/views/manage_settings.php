<div class="page-title">
	<div class="table-display">
		<div class="table-cell valign-middle s12"><h5>Settings</h5></div>
	</div>
</div>

<div class="tabs-wrapper full">
  <div>
    <ul class="tabs row">
    <?php 
    	if( $perm_site )  :
    ?>
	  <li class="tab col s2"><a href="#tab_site_settings" onclick="load_index('tab_site_settings', 'site_settings', '<?php echo CORE_SETTINGS ?>')">Site</a></li>
	 <?php 
	 	endif;
	 ?>
	 <?php 
	 	if( $perm_auth ):
	 ?>
	  <li class="tab col s2"><a class="active" href="#tab_account" onclick="load_index('tab_account_settings', 'account_settings', '<?php echo CORE_SETTINGS ?>')">Authentication</a></li>
	  <?php 
	  	endif;
	  ?>
	  <?php 
	  	if( $perm_media ) :
	  ?>
	  <li class="tab col s2">
	  	<a href="#tab_media_settings" onclick="load_index('tab_media_settings', 'media_settings', '<?php echo CORE_SETTINGS ?>')">Media</a>
	  </li>
	  <?php 
	  	endif;
	  ?>
	  <?php 
	  	if( $perm_dpa ) :
	  ?>
	  <li class="tab col s2">
	  	<a href="#tab_dpa_settings" onclick="load_index('tab_dpa_settings', 'dpa_settings', '<?php echo CORE_SETTINGS ?>')">Data Privacy</a>
	  </li>
	  <?php 
	  	endif;
	  ?>
	  <?php 
	  	if( $perm_sys ) :
	  ?>
	  <li class="tab col s2">
	  	<a href="#tab_sys_settings" onclick="load_index('tab_sys_settings', 'system_settings', '<?php echo CORE_SETTINGS ?>')">Other Settings</a>
	  </li>
	  <?php 
	  	endif;
	  ?>
    </ul>
  </div>
</div>

  <div id="tab_site_settings" class="tab-content col s12"></div>
  <div id="tab_account_settings" class="tab-content col s12"></div>
  <div id="tab_media_settings" class="tab-content col s12"></div>
  <div id="tab_dpa_settings" class="tab-content col s12"></div>
  <div id="tab_sys_settings" class="tab-content col s12"></div>
  
<script type="text/javascript">
/*$(function(){
	set_active_tab('<?php //echo PROJECT_CORE ?>');
});*/
</script>
