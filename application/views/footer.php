<script>
$(function(){
	$('#modal_profile').modal({
		dismissible: false,
		opacity: .5, // Opacity of modal background
		in_duration: 300, // Transition in duration
		out_duration: 200, // Transition out duration
		ready: function() {
			$("#modal_profile .modal-content #content").load('<?php echo base_url() . CORE_USER_MANAGEMENT ?>/profile/modal/');
		}, // Callback for Modal open
		complete: function() { 
		
		} // Callback for Modal close
	});

	$('#modal_version_info').modal({
		dismissible: false,
		opacity: .5, // Opacity of modal background
		in_duration: 300, // Transition in duration
		out_duration: 200, // Transition out duration
		ready: function() {
			$("#modal_version_info .modal-content #content").load('<?php echo base_url() . CORE_SETTINGS ?>/site_info/modal/');
		}, // Callback for Modal open
		complete: function() { 
		
		} // Callback for Modal close
	});
});
</script>
<?php
$pass_data 	= array();
$resources  = array();

if(!EMPTY($load_css)){
foreach($load_css as $css):
echo '<link href="'. base_url() . PATH_CSS . $css .'.css" rel="stylesheet" type="text/css">';
endforeach;
}

if(!EMPTY($load_package_css)){
foreach($load_package_css as $fullPathCss):
echo '<link href="'. $fullPathCss .'.css" rel="stylesheet" type="text/css"></link>';
endforeach;
}

if(!EMPTY($load_base64_css)){
foreach($load_base64_css as $base64css):
echo '<link href="data:text/css;base64,'. $base64css .'" type="text/css"></link>';
endforeach;
}

if(!EMPTY($load_js)){
foreach($load_js as $js):
echo '<script src="'. base_url() . PATH_JS . $js .'.js" type="text/javascript"></script>';
endforeach;
}

if(!EMPTY($load_package_js)){
foreach($load_package_js as $fullPathJs):
echo '<script src="'. $fullPathJs .'.js" type="text/javascript"></script>';
endforeach;
}

if(!EMPTY($load_base64_js)){
foreach($load_base64_js as $base64js):
echo '<script src="data:text/javascript;base64,'. $base64js .'" type="text/javascript"></script>';
endforeach;
}

if( ISSET( $single ) )
{
$resources['single'] = $single;
}

if( ISSET( $multiple ) )
{
$resources['multiple'] = $multiple;
}

if( ISSET( $upload ) )
{
$resources['upload'] = $upload;
}

if( ISSET( $datatable ) )
{
$resources['datatable'] = $datatable;
}

if( ISSET( $load_js ) )
{
$resources['load_js'] = $load_js;
}

if( ISSET( $loaded_init ) )
{
$resources['loaded_init'] = $loaded_init;
}

if( ISSET( $loaded_doc_init ) )
{
$resources['loaded_doc_init'] = $loaded_doc_init;
}

if( ISSET( $load_modal ) )
{
$resources['load_modal'] = $load_modal;
}

if( ISSET( $load_delete ) )
{
$resources['load_delete'] = $load_delete;
}

if( ISSET( $load_materialize_modal ) )
{
	$resources['load_materialize_modal'] = $load_materialize_modal;	
}

if( ISSET( $preload_modal ) )
{
$resources['preload_modal'] = $preload_modal;
}

if( ISSET( $selectize ) )
{
$resources['selectize'] = $selectize;
}

if( ISSET( $paginate ) )
{
$resources['paginate'] = $paginate;
}

if( ISSET( $sumo_select ) )
{
$resources['sumo_select'] = $sumo_select;
}

if( ISSET( $decimal_places ) )
{
$resources['decimal_places'] = $decimal_places;
}

$pass_data['resources']	= $resources;

?>

<?php 
$this->view( 'init/upload_initialization', $pass_data );
$this->view( 'modal_initialization', $pass_data );
$this->view( 'init/materialize_modal_init', $pass_data );
?>

<?php 
$this->view( 'initializations', $pass_data );
?>

