/**
 * @license Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';

	config.toolbarGroups = [
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'links' },
		{ name: 'insert' },
		{ name: 'forms' },
		{ name: 'tools' },
		{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'others' },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
		{ name: 'styles' },
		{ name: 'colors' },
		{ name: 'about' }
	];

    config.removeButtons = 'Source,Underline,Subscript,Superscript,Flash,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,CreateDiv,Smiley,SpecialChar,PageBreak,Iframe,Language,Save,NewPage,DocProps,Preview,Print,Templates,document,ShowBlocks,About';

    config.format_tags = 'p;h1;h2;h3;pre';

	// Simplify the dialog windows.
	config.removeDialogTabs = 'image:advanced;link:advanced;link:upload;image:Upload';
	
	
	// Additionals
	// notification,lineutils,widget,notificationaggregator,filetools,uploadwidget,
	config.extraPlugins 				= 'justify,imageuploader';
	// config.imageUploadUrl 	= '/uploader/upload.php?type=Images';
	config.filebrowserImageBrowseUrl 	= $base_url+'static/js/ckeditor/plugins/imageuploader/imgbrowser.php';
	config.filebrowserUploadUrl 		= $base_url+'upload/upload_ckeditor/';

 	// config.filebrowserImageBrowseUrl =  $base_url+PATH_FILE_UPLOADS;
 	// filebrowserBrowseUrl: '/ckfinder/ckfinder.html',
					// filebrowserImageBrowseUrl: '/ckfinder/ckfinder.html?type=Images',
	// filebrowserUploadUrl: '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
					// filebrowserImageUploadUrl: '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images'
  	// config.filebrowserImageUploadUrl = $base_url+'upload/upload_ckeditor/';
};
