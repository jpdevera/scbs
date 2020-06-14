<div class="page-title">
	<div class="row m-n">
		<div class="col s7 valign-middle p-l-n">
			<!-- <ul class="breadcrumbs m-b-xs">
				<li><a href="<?php echo $workflow_url ?>">Workflow</a></li>
				<li><a href="#!" class="active">Create Workflow</a></li>
			</ul> -->
			<h5>Create Workflow</h5>
		</div>
		<div class="col s5 valign-middle right-align p-r-n">
			<a type="button" id="btn_view_gpb" class="waves-effect waves-light btn grey darken-1 m-r-xs white-text" href="<?php echo $workflow_url ?>"><i class="material-icons">arrow_back</i>Back</a>
		</div>
	</div>
</div>

<div class="wizard-form <?php echo $class_step ?>" id="attributed_program" >
	<ul id="progressbar">
	   <?php echo $list ?>
  	</ul>
	<form>
	<?php 
		if( ISSET( $work_settings_flag['process_flag'] ) AND !EMPTY( $work_settings_flag['process_flag'] ) ) :
	?>
		<?php $this->view('tabs/process', $pass_data) ?>
		
	<?php 
		endif;
	?>

	<?php 
		if( ISSET( $work_settings_flag['stages_flag'] ) AND !EMPTY( $work_settings_flag['stages_flag'] ) ) :
	?>
		<?php $this->view('tabs/stages', $pass_data) ?>
		
	<?php 
		endif;
	?>

	<?php 
		if( ISSET( $work_settings_flag['steps_flag'] ) AND !EMPTY( $work_settings_flag['steps_flag'] ) ) :
	?>
		<?php $this->view('tabs/steps', $pass_data) ?>
		
	<?php 
		endif;
	?>

	<?php 
		if( ISSET( $work_settings_flag['prerequisites_flag'] ) AND !EMPTY( $work_settings_flag['prerequisites_flag'] ) ) :
	?>
		<?php $this->view('tabs/prerequisites', $pass_data) ?>
		
	<?php 
		endif;
	?>
	</form>
  	<fieldset style="display:none !important;" id='main_fieldset'>
		<input type="hidden" id="workflow_main" name="workflow_main" value="<?php echo $main_id ?>">
		<input type="hidden" id="workflow_salt" name="workflow_salt" value="<?php echo $salt ?>">
		<input type="hidden" id="workflow_token" name="workflow_token" value="<?php echo $token ?>">
		<input type="hidden" id="workflow_action" name="workflow_action" value="<?php echo $action ?>">
	</fieldset>
</div>