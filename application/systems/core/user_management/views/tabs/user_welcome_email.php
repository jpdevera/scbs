<?php 
$cancel = "";
$send = "checked";

if(ISSET($user)){
	$send = $cancel = "";
}
?>
<div class="row m-b-n">
	<div class="col s6">
		<div class="input-field">
			<label for="user_send_email" class="active">Send Email</label>
			<input type="radio" name="send_email" id="user_send_email" class="labelauty" data-labelauty="Send a 'Welcome' email to this user" <?php echo $send ?> value="1"/>
		</div>
	</div>
	<div class="col s6">
		<div class="input-field">
			<input type="radio" name="send_email" id="user_cancel_email" class="labelauty" data-labelauty="Don't send a 'Welcome!' email to this user" value="0" <?php echo $cancel ?> />
		</div>
	</div>
</div>
