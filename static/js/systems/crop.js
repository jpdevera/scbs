
var camera; // Initialized at the end

var csrf_name 	= $('meta[name="csrf-name"]').attr('content'),
	csrf_token 	= $('meta[name="csrf-token"]').attr('content');
	pass_data 	= {};

/*	pass_data[csrf_name]	= csrf_token;

$.ajaxSetup({
	beforeSend: function(jqXHR, settings) 
	{
		
		if( csrf_name )	
		{
			if( settings.data )
			{
				settings.data 	+= '&'+csrf_name+'='+csrf_token;	
			}
			else
			{
				settings.data 	= csrf_name+'='+csrf_token;	
			}
			
		}
		
	},
	data 	: pass_data
});
*/

var Crop 	= function()
{
	var cropObj;
	var elem_trigger

	var crop 		= function()
	{
		elem_trigger = $(document.activeElement);

		var image 	= $('#file_image_crop');
		cropObj  	= new Cropper(image[0], {			
			crop: function(e) {

			}
		});
		
		if( cropObj !== undefined )
		{

			$('#crop_rotate_left').on('click', function(e){
				e.stopImmediatePropagation();
				cropObj.rotate(-45);
			});

			$('#crop_rotate_right').on('click', function(e){
				e.stopImmediatePropagation();
				cropObj.rotate(45);
			});

			$('#crop_move').on('click', function(e){
				e.stopImmediatePropagation();
				cropObj.setDragMode("move");
			});

			$('#crop_crop').on('click', function(e){
				e.stopImmediatePropagation();
				cropObj.setDragMode("crop");
			});

			$('#crop_clear').on('click', function(e){
				e.stopImmediatePropagation();
				cropObj.clear();
			});

			$('#crop_flip_vertical').on('click', function(e) {
				e.stopImmediatePropagation();
				if( cropObj.imageData.scaleY == 1 ) 
				{
					cropObj.scaleY(-1);
				}
				else
				{
					cropObj.scaleY(1);
				}
			});

			$('#crop_flip_horizontal').on('click', function(e) {
				e.stopImmediatePropagation();
				if( cropObj.imageData.scaleX == 1 ) 
				{
					cropObj.scaleX(-1);
				}
				else
				{
					cropObj.scaleX(1);
				}
				
			});

			$('#crop_reset').on('click', function(e){
				e.stopImmediatePropagation();
				cropObj.reset();
			});

			$('#crop_zoom_in').on('click', function(e){
				e.stopImmediatePropagation();
				cropObj.zoom(0.1);
			});

			$('#crop_zoom_out').on('click', function(e){
				e.stopImmediatePropagation();
				cropObj.zoom(-0.1);
			});
		}
	
	}

	var crop_save = function()
	{
		$('#submit_modal_crop').on('click', function(e) {

			e.stopImmediatePropagation();
			
			if( cropObj !== undefined )
			{
				var upload_obj;
	    		var upload_id = 'surrenderer_photo';
				if( upload_id != '' )					
				{

					upload_obj 	= eval(upload_id+'_uploadObj');
				}

				setTimeout(function()
				{ 
					var base64 	= cropObj.getCroppedCanvas().toDataURL();
					var bas64_spl = base64.split(',');
					var real_base64 = bas64_spl[1];
					
					var blob 		= b64toBlob(real_base64, 'image/png');


					var formData = new FormData();

  					formData.append('cropped_image', blob);
  					formData.append('file_path', $('#file_path').val());
  					formData.append('file_name', $('#file_name').val());
  					formData.append(csrf_name, csrf_token);

  					 $( "body" ).isLoading({
			        	text:       "<div class='loader'></div>", 
			        	position:   "inside"
			  		});

					 $.ajax($base_url+'Crop/crop', {
					    method: "POST",
					    data: formData,
					    processData: false,
					    beforeSend: function(jqXHR, settings) 
						{
							
						},
					    contentType: false,
					    success: function ( response ) 
					    {
					    }
					  }).promise().done( function( response ) {
					  	response 	= JSON.parse(response);

					  	notification_msg(response.status, response.msg);

				    	if( response.flag )
				    	{

				    		if(  upload_obj )  
				    		{
				    			$('#surrenderer_photo').val('');
				    			upload_obj.fileCounter = 0;
						        upload_obj.selectedFiles = 0;
						        upload_obj.fCounter = 0; //failed uploads
						       	upload_obj.sCounter = 0; //success uploads
						       	upload_obj.tCounter = 0; //total uploads

						        
						        files_arr 				= [];
								files_not_auto_submit 	= [];
								orig_files_arr_main 	= [];
								orig_files_name_arr 	= [];

								$('#surrenderer_photo').val($('file_name'));
				    			upload_obj.recallOnLoad();
				    		}
			    		 	var reader 			= new window.FileReader();
						 	
						 	reader.readAsDataURL(blob); 

						 	var base64data;

						 	reader.onloadend 	= function() 
						 	{
			                	base64data = reader.result;                
				                // console.log(base64data );
				                // elem_trigger.parents('div.ajax-file-upload-statusbar').find('img').removeAttr('src');
				               	elem_trigger.parents('div.ajax-file-upload-statusbar').find('img').attr('src', base64data);
						  	}

				    		$('#modal_crop').closeModal();
				    		var canvas 		= cropObj.getCroppedCanvas();
				    		var context 	= canvas.getContext('2d');

				    		// context.beginPath();
				    		context.clearRect(0, 0, context.canvas.width, context.canvas.height);
				    		context.restore();
				    		
				    		// cropObj.reset();
				    		// cropObj.destroy();
				    		
				    	}
				    	$("body").isLoading("hide");
				    } ).always( function() {
				    	$("body").isLoading("hide");
				    } );
					/*cropObj.getCroppedCanvas().toBlob(function (blob)
					{
					
							
						 
					});*/
				}, 2000);
			}
		});
	}

	var webcam 	= function(upload_id, path, default_check)
	{
		Webcam.set({
			width: 300,
			height: 300,
			image_format: 'png',
			enable_flash : false,
			jpeg_quality: 90,
			constraints: {
				width: { exact: 300 },
				height: { exact: 300 }
			}
			// force_flash : true
		});

	 	Webcam.set("constraints", {
    		optional: [{ minWidth: 600 }]
      	});

		Webcam.attach( '#camera' );
	}

	var b64toBlob 		= function (b64Data, contentType, sliceSize) 
	{
        contentType 	= contentType || '';
        sliceSize 		= sliceSize || 512;

        var byteCharacters 	= atob(b64Data);
        var byteArrays 		= [];

        for ( var offset = 0; offset < byteCharacters.length; offset += sliceSize) 
        {
            var slice 		= byteCharacters.slice(offset, offset + sliceSize);

            var byteNumbers = new Array(slice.length);

            for (var i = 0; i < slice.length; i++) 
            {
                byteNumbers[i] = slice.charCodeAt(i);
            }

            var byteArray 		= new Uint8Array(byteNumbers);

            byteArrays.push(byteArray);
        }

      var blob = new Blob(byteArrays, {type: contentType});

      return blob;
    }

	var take_snapshot 	= function(upload_id, path, default_check)
	{
		var btn 		= $('#submit_modal_webcam'),
			form 		= $('#form_modal_webcam')
		
		btn.on('click', function( e )
		{
			e.preventDefault();
			e.stopImmediatePropagation();

			Webcam.snap(function(uri, canvas)
			{
				var image;

				image 	= canvas.toDataURL();

				var block 		= image.split(";");
                // Get the content type
                var contentType = block[0].split(":")[1];// In this case "image/gif"
                // get the real base64 content of the file
                var realData 	= block[1].split(",")[1];// In this case "iVBORw0KGg...."

                // Convert to blob
                var blob 		= b64toBlob(realData, contentType);

				var data 		= new FormData(form[0]);

				var upload_opt;
				var upload_obj;
				
				if( default_check )
				{
					// upload_opt 	= default_uploadObj_opt || {};
					if( typeof( uploadObj ) !== 'undefined' )
					{
						upload_obj 	= uploadObj;
					}
				}
				else
				{
					if( upload_id != '' )					
					{

						upload_opt 	= eval(upload_id+'_options_upload_obj');
						upload_obj 	= eval(upload_id+'_uploadObj');
					}
				}

				if( upload_opt !== undefined )
				{
					data.append("path", upload_opt.formData.dir);
				}
				else
				{
					data.append("path", path);	
				}

            	data.append("webcam_photo", blob);
				data.append(csrf_name, csrf_token);

				save_funct(data, upload_id, upload_obj, default_check, path);
			});
		});
	}

	var save_funct 		= function(data, upload_id, upload_obj, default_check, path)
	{
		$( "body" ).isLoading({
        	text:       "<div class='loader'></div>", 
        	position:   "overlay"
  		});

		$.ajax({
	 		url 	: $base_url + 'Crop/upload_photo',
	 		beforeSend: function(jqXHR, settings) 
			{
				
			},
	 		data 	: data,// the formData function is available in almost all new browsers.
            type 	:"POST",
            contentType 	:false,
            processData 	:false,
            cache 			:false,
            method 			: 'POST',
		}).promise().done( function( response ) 
		{
			response	= JSON.parse(response);

			if( response.flag ) 
			{
				switch(upload_id)
				{
					case 'surrenderer_photo' :
						$('#surrenderer_photo').val(response.file_name);
						upload_obj.recallOnLoad();
						$('a[href="#modal_webcam"]').attr('style', 'display:none !important');
						$('#surrenderer_photo_src').attr('src', '');
					break;
					case 'system_logo':
						$('#system_logo').val(response.file_name);
						upload_obj.recallOnLoad();
						$('a[href="#modal_webcam"]').attr('style', 'display:none !important');
						$('#system_logo_src').attr('src', '');
					break;

					case 'photo_citizen' :
						$('#user_image').val(response.file_name);
						upload_obj.recallOnLoad();
						$('a[href="#modal_webcam"]').attr('style', 'display:none !important');
						$('#profile_img').attr('src', '');
					break;

					case 'photo_citizen_issuance' :
						$('#user_image').val(response.file_name);
						$('#profile_img').attr('src', $base_url+path+response.file_name);
					break;
				}

				Webcam.reset();

				$('#modal_webcam').modal("close");
			}

			$("body").isLoading("hide");

			notification_msg(response.status, response.msg);
		});
	} 

	var capture 		= function(snapshot)
	{
		var canvas, context, crop, scale, _options;

		if( camera )
		{
			_options 		= snapshot._options();
		 	scale 			= Math.min(1.0, _options.scale);
      		scale 			= Math.max(0.01, scale);

			crop 			= camera._get_capture_crop();
	        canvas 			= document.createElement("canvas");
	        canvas.width 	= Math.round(crop.width * scale);
	        canvas.height 	= Math.round(crop.height * scale);
	        context 		= canvas.getContext("2d");
	        // console.log(camera.getVideoObj());
	        context.drawImage(camera.getVideoObj(), crop.x_offset, crop.y_offset, crop.width, crop.height, 0, 0, Math.round(crop.width * scale), Math.round(crop.height * scale));

	        return canvas;
		}
	}

	return {
		init : function()
		{
			crop();
		},
		crop : function()
		{
			crop_save();
		},
		webcam : function(upload_id, path, default_check)
		{
			webcam(upload_id, path);
			take_snapshot(upload_id, path, default_check);
		}
	}
}();
