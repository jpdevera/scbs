<style type="text/css">
table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
}
</style>
<div class="form-basic p-md p-t-lg">
	<div class="p-lg m-md red lighten-5" style="border: 1px solid #ffcdd2; border-radius:2px;">
		<span class="font-bold">WARNING</span>&nbsp;&nbsp;An error occurred while trying to upload the template. Please check the error logs listed below and try again.
		<br/><br/>
		<a target="_blank" href="<?php echo $path_upl ?>">Please click this link and redownload the template then correct the necessary detail(s).</a>
	</div>
	<table class="table table-default" id="error_import_tbl">
		<thead>
			<?php 
				if( !EMPTY( $params['header'] ) ) :

					$width = 100 / count( $params['header'] );
					$width = abs(round($width));
			?>
			<tr >
				<th></th>
				<?php 
					foreach( $params['header'] as $heade ) :
				?>
				<th class="black-text"><?php echo $heade ?></th>
				<?php 
					endforeach;
				?>
			</tr>
			<?php 
				endif;
			?>
		</thead>
		<tbody>
			<?php 
				if( !EMPTY( $params['upl_arr'] ) ) :
			?>
				<?php 
					foreach( $params['upl_arr'] as $upl_arr ) :
				?>
					<tr class="green lighten-4">
						<td></td>
					<?php 
						foreach( $params['header'] as $head ) :
					?>
					
						<td>
							
							<?php 
								echo $upl_arr[$head];
							?>
						</td>
					
					<?php 
						endforeach;
					?>
					</tr>
					<tr>
						<td class="green-text" colspan="<?php echo count($params['header']) ?>">
							<b>Details for Row <?php echo $upl_arr['row_index'] ?> was saved.</b>
						</td>
					</tr>
				<?php
					endforeach;
				?>
			<?php 
				endif;
			?>
			<?php 
				if( !EMPTY( $params['err_arr'] ) ) :
			?>
				<?php 
					foreach( $params['err_arr'] as $err_arr ) :
				?>
					<tr class="red lighten-4">
						<td></td>
					<?php 
						foreach( $params['header'] as $head ) :
					?>
					
						<td>
							
							<?php 
								echo $err_arr[$head];
							?>
						</td>
					
					<?php 
						endforeach;
					?>
					</tr>
					<tr>
						<td class="red-text" colspan="<?php echo count($params['header']) ?>">
							<b>Errors for Row <?php echo $err_arr['row_index'] ?> in excel template.</b>
							<br/>
							<br/>
							<ul>

							<?php 
								
								$error_msg = $err_arr['error_msg'];
								echo $error_msg;
							?>
							</ul>
						</td>
					</tr>
				<?php
					endforeach;
				?>
			<?php 
				endif;
			?>
		</tbody>
	</table>
</div>