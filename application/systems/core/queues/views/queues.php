<div class="page-title">
	<div class="table-display">
		<div class="table-cell valign-middle s12"><h5>Queues</h5></div>
	</div>
</div>
<div class="tabs-wrapper full">
	<div>
		<ul class="tabs row">
			<?php 
		    	if( $perm_email_q )  :
		    ?>
			  <li class="tab col s6"><a href="#tab_email_queues" onclick="load_index('tab_email_queues', 'email_queues', '<?php echo CORE_QUEUES ?>')">Email Queues</a></li>
			 <?php 
			 	endif;
			 ?>
			 <?php 
		    	if( $perm_sms_q )  :
		    ?>
			  <li class="tab col s6"><a href="#tab_sms_queues" onclick="load_index('tab_sms_queues', 'sms_queues', '<?php echo CORE_QUEUES ?>')">Sms Queues</a></li>
			 <?php 
			 	endif;
			 ?>
		</ul>
	</div>
</div>

<div id="tab_email_queues" class="tab-content col s12"></div>
<div id="tab_sms_queues" class="tab-content col s12"></div>