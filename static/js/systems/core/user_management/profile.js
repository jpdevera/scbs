var Profile = function()
{
	var $module = "user_management";
		$path_uploads = $("#user_upload_path").val();
		$path_images = "static/images/";
	var csrf_name 	= $('meta[name="csrf-name"]').attr('content'),
		csrf_token 	= $('meta[name="csrf-token"]').attr('content');

	var form_data 	= {
		"dir": $path_uploads.replace('\\', '\\\\')
	};
	if( csrf_name )
	{
		form_data[csrf_name] 	= csrf_token;
	}
		
	var initForm = function()
	{
		$('#link_tab_profile_account').trigger('click');
	}
	
	var initObj = function()
	{
		$('#facebook_email_same').on('click', function(e)
		{
			e.preventDefault();

			var self 	= $(this);
			var check_c = self.is(':checked');

			$('#confirm_modal').confirmModal({
				topOffset : 0,
				onOkBut : function() {
					if( self.is(':checked') )
					{
						self.prop('checked', false);
					}
					else
					{
						self.prop('checked', true);
					}
					
					if( self.is(':checked') )
					{
						$('#facebook_email').attr('readonly', 'readonly');
						$('#facebook_email').val($('#email_hid').val());
					}
					else
					{
						$('#facebook_email').removeAttr('readonly');
						$('#facebook_email').val('');	
					}
				},
				onCancelBut : function()
				{
					if( self.is(':checked') )
					{
						self.prop('checked', true);
					}
					else
					{
						self.prop('checked', false);
					}
				},
				onLoad : function() 
				{
					if( self.is(':checked') )
					{
						self.prop('checked', false);
					}
					else
					{
						self.prop('checked', true);	
					}

					if( check_c )
					{
						msg 	= 'Are you sure you want to use system email '+$('#email_hid').val()+' ?';
					}
					else
					{
						msg 	= 'Are you sure you want to use a different email?';	
					}

					$('.confirmModal_content h4').html(msg);	
				},
				onClose : function() {}
			});

		});

		$('#google_email_same').on('click', function(e)
		{
			e.preventDefault();

			var self 	= $(this);
			var check_c = self.is(':checked');

			$('#confirm_modal').confirmModal({
				topOffset : 0,
				onOkBut : function() {
					if( self.is(':checked') )
					{
						self.prop('checked', false);
					}
					else
					{
						self.prop('checked', true);
					}
					
					if( $(this).is(':checked') )
					{
						$('#google_email').attr('readonly', 'readonly');
						$('#google_email').val($('#email_hid').val());
					}
					else
					{
						$('#google_email').removeAttr('readonly');
						$('#google_email').val('');	
					}
				},
				onCancelBut : function()
				{
					if( self.is(':checked') )
					{
						self.prop('checked', true);
					}
					else
					{
						self.prop('checked', false);
					}
				},
				onLoad : function() 
				{
					if( self.is(':checked') )
					{
						self.prop('checked', false);
					}
					else
					{
						self.prop('checked', true);	
					}
					
					if( check_c )
					{
						msg 	= 'Are you sure you want to use system email '+$('#email_hid').val()+' ?';
					}
					else
					{
						msg 	= 'Are you sure you want to use a different email?';	
					}

					$('.confirmModal_content h4').html(msg);	
				},
				onClose : function() {}
			});
		});
		toggle_fields('PROFILE');
		var $allowed_types = "jpeg,jpg,png,gif";
		uploadObj = $("#profile_photo").uploadFile({
			url: $base_url + "upload/",
			fileName: "file",
			allowedTypes:$allowed_types,
			acceptFiles:"*",
			dragDrop:false,
			multiple: false,
			maxFileCount: 1,
			allowDuplicates: true,
			duplicateStrict: false,
			showDone: false,
			showAbort: false,
			showProgress: false,
			showPreview: false,
			returnType:"json",	
			formData: form_data,
			uploadFolder:$base_url + $path_uploads.replace('\\', '\\\\'),
			onSelect: function(files){
				$('div.ajax-file-upload-error').addClass('none');
				$(".avatar-wrapper .ajax-file-upload").hide();
				
				$(".avatar-wrapper .ajax-file-upload").css("display","");
			},
			onProgress:function()
			{
				$(".avatar-wrapper .ajax-file-upload form:first").remove();
				start_loading();
			},
			onError: function(files,status,errMsg)
			{       
		    		end_loading();  
		    		console.log($base_url + $path_uploads.replace('\\', '\\\\'));
				$(".avatar-wrapper .ajax-file-upload").hide();
				$(".avatar-wrapper .ajax-file-upload").css("display","");
				
				$(document).ready(function(){$(".avatar-wrapper .ajax-file-upload-statusbar .ajax-file-upload-error").remove();	});
		    	notification_msg('error', 'Invalid File');
			},
			onSuccess:function(files,data,xhr){ 
				end_loading();
		    		if( CONSTANTS.CHECK_CUSTOM_UPLOAD_PATH )
				{
					var avatar = output_image(data[0], $path_uploads);
				}
				else
				{
					var avatar = $base_url + $path_uploads + data;
				}
				$("#profile_img").attr("src", avatar);
				
				$('#user_image').val((data));
				$(".avatar-wrapper .ajax-file-upload").css("display","");
				$('.avatar-wrapper .ajax-file-upload-progress').hide();
				$(".avatar-wrapper .ajax-file-upload-red").html("<i class='flaticon-recycle69'></i>");
			},
			showDelete:true,
			deleteCallback: function(data,pd)
			{
				for(var i=0;i<data.length;i++)
				{
					$.post($base_url + "upload/delete/", {op : "delete", name : data[i], dir : $path_uploads},
					function(resp, textStatus, jqXHR)
					{ 
						$(".avatar-wrapper .ajax-file-upload-error").fadeOut();	
						$('#user_image').val(''); 
						
						var avatar = $base_url + $path_images + "avatar.jpg";
						$("#profile_img").attr("src", avatar);
					});
				 }      
				pd.statusbar.hide();
				$(".avatar-wrapper .ajax-file-upload").css("display","");
			},
			onLoad:function(obj)
			{
				$.ajax({
					cache: true,
					url: $base_url + "upload/existing_files/",
					dataType: "json",
					data: { dir: $path_uploads, file: $('#user_image').val()} ,
					success: function(data) 
					{
						for(var i=0;i<data.length;i++)
						{
							obj.createProgress(data[i]);
						}	
						
						if(data.length > 0){
						  $(".avatar-wrapper .ajax-file-upload").hide();
						  $('.avatar-wrapper .ajax-file-upload-progress').hide();
						  $(".avatar-wrapper .ajax-file-upload-red").html("<i class='material-icons'>cancel</i>");
						}else{
						  var avatar = $base_url + $path_images + "avatar.jpg";
						  $("#profile_img").attr("src", avatar);
						}
					}
				});
			}
		});
	}
	
	var initModal = function($pass_length, $upper_length, $digit_length, $pass_err)
	{
		$('#current_password').blur(function() {
			var password = $(this).val();
			
			if(password != ''){
			  $(this).addClass('loading');
			  
				$.post($base_url + $module + "/profile/validate_password_prof/", { password : password },function(result){
					if(result.flag == 0){
					  $("#new_password, #confirm_password").closest(".col").addClass("disabled");
					  $('#new_password, #confirm_password').prop('disabled',true);
					  $('#current_password').addClass('error-loading').removeClass('loading success-loading');
					  
					  $("#new_password, #confirm_password").attr("data-parsley-required", "false");
					} else {
					  $("#new_password, #confirm_password").closest(".col").removeClass("disabled");
					  $('#new_password, #confirm_password').prop('disabled',false);
					  $('#current_password').addClass('success-loading').removeClass('loading error-loading');
					  
					  $("#new_password, #confirm_password").attr("data-parsley-required", "true");
					}
				}, 'json');
			} else {
				$(this).removeClass('loading error-loading success-loading');
				$('#new_password, #confirm_password').prop('disabled',true);
				$("#new_password, #confirm_password").attr("data-parsley-required", "false");
			}
			
		});

		/*window.ParsleyValidator.addValidator('pass', function (input, data_val) {
			var input_copy = input;
			var input_count = input.length;
			var upper_count = input_copy.replace(/[^A-Z]/g, "").length;
			var digit_count = input_copy.replace(/[^0-9]/g, "").length;
				
			if($pass_length > 0){

				if(input_count < parseInt($pass_length) && parseInt($pass_length) != 0)
					return false;

				if(upper_count < parseInt($upper_length) && parseInt($upper_length) != 0)
					return false;

				if(digit_count < parseInt($digit_length) && parseInt($digit_length) != 0)
					return false;
				
			}
			
			return true;
			
		}).addMessage('en', 'pass', $pass_err);*/
	}	
	
	var save = function()
	{
		$('#form_profile_account').parsley();
		$("#submit_profile_account").on("click", function(){

			  if ( $("#form_profile_account").parsley().isValid() ) {
				  var data = $("#form_profile_account").serialize();
				  
				  button_loader('submit_profile_account', 1);
				  
				  $.post($base_url + $module + "/profile/process", data, function(result) {
					  notification_msg(result.status, result.msg);
					  button_loader('submit_profile_account', 0);
					  
					  if(result.status == "success")
					  {
						  $("#top_bar_account_name").text(result.name);

						  if( result.image != "" )
						  {

					  		if( CONSTANTS.CHECK_CUSTOM_UPLOAD_PATH )
							{
								var avatar = output_image(result.image, $path_uploads);
							}
							else
							{
								var avatar = $base_url + $path_uploads + result.image;
							}
						 }
						 else
						 {
						 	var avatar 	= $base_url + $path_images + "avatar.jpg";
						 }
						  
						  $("#top_bar_avatar").attr("src", avatar);
					  }
						
				  }, 'json');
			  }
		});
	}
	
	var savePassword = function()
	{
		$('#form_profile_pass').parsley();
		$("#submit_profile_pass").on("click", function(){

			  if ( $("#form_profile_pass").parsley().isValid() ) {
				  var data = $("#form_profile_pass").serialize();
				  
				  button_loader('submit_profile_pass', 1);
				  
				  $.post($base_url + $module + "/profile/process/1", data, function(result) {
					  notification_msg(result.status, result.msg);
					  button_loader('submit_profile_pass', 0);
					  
					  if(result.status == "success")
					  {
						  $('#current_password').val("").removeClass('loading error-loading success-loading');
						  $('#new_password, #confirm_password').prop('disabled',true);
						  $("#new_password, #confirm_password").val("").attr("data-parsley-required", "false");
					  }
						
				  }, 'json');
			  }
		});
	}
	return {
		initObj : function()
		{
			initObj();
		},
		initForm : function()
		{
			initForm();
		},
		initModal : function($pass_length, $upper_length, $digit_length, $pass_err)
		{
			initModal($pass_length, $upper_length, $digit_length, $pass_err);
		},
		save : function()
		{
			save();
		},
		savePassword : function()
		{
			savePassword();
		}
	}
}();
