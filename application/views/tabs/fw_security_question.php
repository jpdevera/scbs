<div class="p-md form-basic left-align">
	<div class="row m-b-n">
		<div class="title-content font-normal">Security Question</div>
		<div class="fs-subtitle m-t-sm">Please answer your security questions.</div>
	</div>
	<div id="sec_ques_div" class="black-text">
		<?php 
			if( ISSET( $security_question_view ) AND !EMPTY( $security_question_view ) ) :
		?>
			<?php echo $security_question_view ?>
		<?php 
			endif;
		?>
	</div>
</div>
<?php 
	if( !$no_next ) :
?>
<input type="button" name="next" id="process_save" data-action="ForgotPw.move_sec_answer(animate_next, next_fs, current_fs, self);" class="next action-button" value="Next" />
<?php 
	endif;
?>