<li class="list-item" style="border-style:none !important;">
	<div class="list-image">
		<div class="<?php echo $details['file_extension'] ?>">
			<div class="list-counter"><?php echo $version ?></div>
		</div>
	</div>
	<div class='row m-b-n p-t-xs list-details'>
		<div class='col l6 m11 s12 center-oso'>
			<p class='m-n font-md font-semibold'>
				<?php echo $details['display_name'] ?>
				<span class='font-sm mute m-l-sm'>
					<?php echo 'v'.$version ?>
				</span>
				<p class='truncate m-b-xs m-t-xs'>
					by <strong><?php echo $details['created_fullname'] ?></strong> • <span class="purple-text"><?php echo $file_size; ?></span>
				</p>
				<p class='mute font-sm'>
					<span class='m-r-sm'>
						<i class='material-icons'>access_time</i><?php echo $details['created_date_format'] ?>
					</span>
					<i class='flaticon-tag68'>
						<?php //echo $r['tags'] ?>
						<?php //echo $file_size_num ?>
					</i>
				</p>
			</p>
		</div>
		<div class='col l6 m11 s12 right-align center-oso left-omo'>
			<ul class='list-action-buttons circles p-n' >
				<?php 
					if( $view_per AND $show_view ) :

						if( $file_size_num > 1000000 ) :
				?>
					<!-- <li ><a class="btn-floating btn-medium waves-effect waves-light tooltipped" data-file-name="<?php //echo $file_name ?>" data-file-ext="<?php //echo $ext ?>" target="_blank" data-tooltip='Preview' href="<?php echo $force_url ?>"><i class="material-icons">open_in_new</i></a> -->
					<li ><a class="btn-floating btn-medium waves-effect waves-light tooltipped" data-file-name="<?php //echo $file_name ?>" data-file-ext="<?php //echo $ext ?>" target="_blank" data-tooltip='Preview' data-file="<?php echo $file_name ?>" onclick="viewerjs(this, event)" href="<?php echo $preview_url ?>"><i class="material-icons">open_in_new</i></a>
					<?php
						else :
					?>
					<!-- <li ><a class="btn-floating btn-medium waves-effect waves-light tooltipped view_btn" data-file-name="<?php //echo $file_name ?>" data-file-ext="<?php //echo $ext ?>" data-tooltip='View PDF' onclick="Files.file_content( this )"><i class="material-icons">open_in_new</i></a> -->
					<li ><a class="btn-floating btn-medium waves-effect waves-light tooltipped" data-file-name="<?php //echo $file_name ?>" data-file-ext="<?php //echo $ext ?>" target="_blank" data-tooltip='Preview' data-file="<?php echo $file_name ?>" onclick="viewerjs(this, event)" href="<?php echo $preview_url ?>"><i class="material-icons">open_in_new</i></a>
					</li>
					<?php
						endif;
					?>
				<?php 
					endif;
				?>
				
				<?php 
					if( $download_per AND $show_dl ) :
				?>
				<li >
					<a class='btn-floating btn-medium waves-effect waves-light tooltipped' target="_blank" data-tooltip='Download' href="<?php echo $force_url.'&pdf_no=1' ?>">
						<i class='material-icons'>file_download</i>
					</a>
				</li>
				<?php 
					endif;
				?>
				<?php 
					if( $version_per AND $show_vl ) :
				?>
				<li>
					<a class="btn-floating btn-medium waves-effect waves-light tooltipped" href="#modal_version_list" data-tooltip='Versions' onclick="modal_version_list_init('', this)" data-modal_post='<?php echo $orig_params_json ?>'>
						<i class="material-icons">sort</i>
					</a>
				</li>
				<?php 
					endif;
				?>
				<?php 
					if( $show_act ) :
				?>
				<li>
					<a class="btn-floating btn-medium waves-effect waves-light dropdown-button more tooltipped" href="#!" data-tooltip='More' data-activates="dropdown_<?php echo $key ?>" >
						<i class="material-icons">expand_more</i>
					</a>
					<ul id="dropdown_<?php echo $key ?>" class="box-shadow dropdown-content">
					<?php 
						if( $edit_per ) :
					?>
					<li>
						<a href="#modal_upload_file" data-modal_post='<?php echo $orig_params_json ?>' class="" onclick="modal_upload_file_init('<?php echo $edit_url ?>', this)">
							<i class="material-icons">mode_edit</i> Edit
						</a>
					</li>
					<?php 
						endif;
					?>
					<?php 
						if( $delete_per ) :
					?>
					<li>
						<a href="#!" onclick="content_file_delete('File', '<?php echo $del_url ?>', '', this)" data-delete_post='<?php echo $orig_params_json ?>' data-modal_post='<?php echo $orig_params_json ?>' >
							<i class="material-icons">delete</i> Delete
						</a>
					</li>
					<?php 
						endif;
					?>
					<?php 
						if( $version_per ) :
					?>
					<li>
						<a href="#modal_upload_file_version" data-modal_post='<?php echo $orig_params_json ?>' class="" id="" onclick="modal_upload_file_version_init('', this)" >
							<i class="material-icons">backup</i> Upload a new version
						</a>
					</li>
					<?php 
						endif;
					?>
				</li>
				<?php 
					endif;
				?>
			</ul>
		</div>
	</div>
</li>