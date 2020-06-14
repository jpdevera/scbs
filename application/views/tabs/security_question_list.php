<?php 
	$req = ( ISSET( $req_pass ) ) ? $req_pass : 'true';
		if( !EMPTY( $security_questions ) ) :
	?>
	<!-- <div class="row m-b-n">	
		<div class="col s12"><h5 class="form-subtitle">Security Questions</h5></div>
	</div> -->
	<?php 
		foreach( $security_questions as $secs ) :

			$id_sec = base64_url_encode($secs['security_question_id']);
	?>
	<div class="row m-b-n">	
		
		<div class="col s6">
			<input type="hidden" name="security_question_id[]" value="<?php echo $id_sec ?>">
			<input type="hidden" name="us_answ[]" value="<?php echo base64_url_encode( $secs['answer'] ) ?>">
			<input type="hidden" name="sec_text[]" value="<?php echo base64_url_encode( $secs['question'] ) ?>">
			<p><?php echo $secs['question'] ?></p>
		</div>
		<div class="col s6 m-t-md">
			<!-- <div class="input-field">
				<div> -->
					<input type="text" data-parsley-required="<?php echo $req ?>" name="security_question_answers[]" data-parsley-group="fieldset-<?php echo $data_page_key ?>" data-parsley-trigger="keyup" value="" id="security_question_answers">
				<!-- </div>
			</div> -->
		</div>
	</div>
	<?php 
		endforeach;
	?>
	<?php 
		endif;
	?>