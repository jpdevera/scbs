<?php 
	
	$bg_class 	= '';
	$row_class	= 'p-xl';

	if( ISSET( $modal ) AND !EMPTY( $modal ) )
	{
		$bg_class 	= 'bg white';
		$row_class 	= 'm-n';
	}
?>
<form>
<div class="col s12">

	<div class="row <?php echo $row_class ?>">

		<div class="col s12 p-t-md center-align">
			<h5 class="red-text text-darken-4"> <?php echo $err_status ?></h5>
		</div>
		    

		<div class="col s12 center-align p-b-md">
			<h6 class="<?php echo $bg_class ?> red-text text-darken-4">
				<?php echo $heading  ?>
			</h6>
			<div class="col s12">
				<p class="center-align">
					<?php echo $message ?>
				</p>
			</div>
		</div>

	</div>
	
</div>
<?php 
	if( ISSET( $modal ) AND !EMPTY( $modal ) ) :
?>
<div class="md-footer default">	  
	<!-- <a class="waves-effect waves-teal btn-flat cancel_modal" id="cancel_error">Close</a> -->
</div>
<?php 
	endif;
?>
</form>