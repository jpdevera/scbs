<div class="page-title">
	<div class="table-display">
		<div class="table-cell valign-middle s6"><h5><?=$title?></h5></div>
	</div>
</div>
<div class="form-basic panel m-md p-md">
	<div class="tabs-wrapper full">
		<div>
	    	<ul class="tabs row">
				<li class="tab col s6">
		  			<a href="#tab_branch_info" onclick="">
	  					<i class="material-icons">folder</i>
		  				<span class="hide-on-med-and-down">Branch Information</span>
		  			</a>
		  		</li>
		  		<li class="tab col s6">
		  			<a href="#tab_branch_setup" onclick="">
		  				<i class="material-icons">folder</i>
		  				<span class="hide-on-med-and-down">Branch Setup</span>
		  			</a>
		  		</li>
		  	</ul>
		</div>
	</div>
</div>
<div class="form-basic panel m-md p-md">
	<form id="form_branch" name="form_branch" class="form-vertical form-styled" autocomplete="off">
		<input type="hidden" name="security" value="<?php echo $security ?>">
		<div id="tab_branch_info">
			<?php echo $tab_branch_info; ?>
		</div>
		<div id="tab_branch_setup">
			<?php echo $tab_branch_setup; ?>
		</div>
	</form>	
</div>