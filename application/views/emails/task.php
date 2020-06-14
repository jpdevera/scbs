<?php 
	$base_task_id = base64_url_encode($task_id);
	$base_project_id = base64_url_encode($project_id);
	$url = $base_task_id.'/'.$base_project_id;
?>
<div style="width:85%; margin:0 auto;">
  <div style="background:#f2c65d; padding:20px 30px;"><img src="<?php echo base_url() . PATH_IMAGES ?>logo_new2.png" height="40" alt="logo" /></div>
  <div style="background:#f6f6f6; padding:30px;">
    
	<?php if(!EMPTY($comments) && !$is_attachment && $is_comment){ ?>
	  <div style="font-size:14px; font-weight:600; margin:0px 0 10px; font-family:'Open Sans', arial; text-transform:uppercase;">Comments</div>
	  <?php foreach($comments as $comment): ?>
		<div style="padding:0; border-bottom:1px dashed #e5e5e5;">
		  <div style="display:table; width:100%;">
			<div style="display:table-cell; width:150px; vertical-align:top;">
			  <p style="font-size:13px; color:#aaa; font-family:'Open Sans', arial;margin:10px 0; line-height:25px;">
				<?php echo date('m/d/Y h:i a', strtotime($comment['comment_date'])) ?>
			  </p>
			</div>
			<div style="display:table-cell; vertical-align:top;">
			  <p style="font-size:14px; font-family:'Open Sans', arial;margin:10px 0; line-height:25px;">
				<strong><?php echo $comment['name'] ?> </strong><br/><?php echo $comment['comment'] ?>
			</div>
		  </div>
		</div>
	  <?php endforeach; ?>
	<?php } ?>	
	
	<?php 
	  if(!$is_comment){
	    if(!$is_attachment || ISSET($is_reminder)){
	      $main_title = (ISSET($is_reminder)) ? "This is a reminder for " : $assigned_by . " has assigned a task to you.";
	?>
	
	<?php if($action != AUDIT_UPDATE){ ?>
	  <p style="font-size:18px; font-weight:bold; font-family:'Open Sans', arial;margin:10px 0; line-height:25px;"><?php echo $main_title ?></p>
	<?php } ?>
	
	<p style="font-size:15px; font-family:'Open Sans', arial;margin:10px 0; line-height:25px;"><?php echo $task_name ?> <a style="font-family:'Open Sans', arial; margin-left:5px; line-height:25px;" title="View Task" href="<?php echo base_url() . MOD_PMS ?>/home/task/<?php echo $url ?>">View</a></p>
	<?php }else{ ?>
	  <p style="font-size:18px; font-weight:bold; font-family:'Open Sans', arial;margin:10px 0; line-height:25px;"><?php echo $this->session->userdata('name') ?> uploaded a new file.</p>
	  <div style="border:1px solid #E3DEB5; background:#FBF7DC; border-radius:2px; padding:15px; word-break:keep-all; margin-top:20px;">
		<ul style="list-style-type: square; margin:0; padding: 0 15px;">
	    <?php foreach($new_files as $file): ?>
		  <li class="padding:5px 0;"><a style="font-size:13px; font-family:'Open Sans', arial; text-decoration:none; line-height:25px;" href="<?php echo base_url() . PATH_TASK_UPLOADS . $file['filename'] ?>"><?php echo $file['filename']; ?></a></li>
	    <?php endforeach; ?>
	    </ul>
	  </div>
	<?php 
		} 
	  } 
	?>
	
	<div style="border:1px solid #E3DEB5; background:#FBF7DC; border-radius:2px; padding:15px; word-break:keep-all; margin-top:20px;">
		<div style="font-size:14px; font-weight:600; margin-bottom:10px; font-family:'Open Sans', arial; text-transform:uppercase;">Task Details</div>
		<p style="font-size:14px; font-family:'Open Sans', arial;margin:10px 0; line-height:25px;">
			<?php if($is_attachment || $is_comment){ ?>
				<strong>Task: </strong> <?php echo $task_name ?> <a style="font-family:'Open Sans', arial; margin-left:5px; line-height:25px;" title="View Task" href="<?php echo base_url() . MOD_PMS ?>/home/task/<?php echo $url ?>">View</a><br/>
			<?php } ?>
			<strong>Start Date: </strong> <?php echo $task_start_date ?><br/>
			<strong>Due Date: </strong> <?php echo $task_due_date ?><br/>
			<strong>Assigned To: </strong> <?php echo $assigned_to ?><br/>
			<strong>Assigned By: </strong> <?php echo $assigned_by ?><br/>
			<strong>Task List: </strong> <?php echo $task_list ?>
		</p>
	  
		<div style="font-size:14px; font-weight:600; margin:20px 0 10px; font-family:'Open Sans', arial; text-transform:uppercase;">Project Details</div>
		<p style="font-size:14px; font-family:'Open Sans', arial;margin:10px 0; line-height:25px;">
			<strong>Initiative: </strong> <?php echo $initiative ?><br/>
			<strong>Lead Agency: </strong> <?php echo $lead_agency ?><br/>
		</p>
		
		<?php if(!EMPTY($attachments) && !$is_attachment){ ?>
		<div style="font-size:14px; font-weight:600; margin:35px 0 10px; font-family:'Open Sans', arial; text-transform:uppercase;">Attached Files</div>
		<p style="font-size:14px; font-family:'Open Sans', arial;margin:10px 0; line-height:25px;">
			<ul style="list-style-type: square; margin:10px 10px 10px 15px">
			  <?php foreach($attachments as $attachment): ?>
				<li class="padding:5px 0;"><a style="font-size:13px; font-family:'Open Sans', arial; text-decoration:none; line-height:25px;" href="<?php echo base_url() . PATH_TASK_UPLOADS . $attachment['filename'] ?>"><?php echo $attachment['filename']; ?></a></li>
			  <?php endforeach; ?>
			</ul>
		</p>
		<?php } ?>
	</div>
	
	<p style="font-size:13px; color:#999; text-shadow: 0 0 2px #fff; font-family:'Open Sans', arial;margin:30px 0 0; line-height:25px;">
		This email is sent automatically by the system, please do not reply directly. 
	</p>
  </div>
</div>
