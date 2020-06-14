<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Params extends SYSAD_Controller 
{

    public function __construct() 
    {
        parent::__construct();
    }

    public function get_constants_ajax()
    {
        $constants_arr          = array();

        try
        {
        
            /* Get all the constants that starts with CORE_ */
            $core_module_folder     = $this->get_constants('^CORE_');
            $systems                = $this->get_constants('^SYSTEM_');
            $actions                = $this->get_constants('^ACTION_');
            $core_stat_active       = $this->get_constants('ACTIVE');
            $core_stat_pending      = $this->get_constants('PENDING');
            $core_stat_inactive     = $this->get_constants('INACTIVE');
            $core_stat_approved     = $this->get_constants('APPROVED');
            $core_stat_disapp       = $this->get_constants('DISAPPROVED');
            $core_stat_deleted      = $this->get_constants('DELETED');
            $core_stat_blocked      = $this->get_constants('BLOCKED');
            $core_stat_draft        = $this->get_constants('DRAFT');
            $core_stat_expired      = $this->get_constants('EXPIRED');
            $dpa_type               = $this->get_constants('^DATA_PRIVACY_TYPE_');
            $media_upl_type         = $this->get_constants('^MEDIA_UPLOAD_TYPE_');
            $statement_type         = $this->get_constants('^STATEMENT_TYPE_');
            $actions                = $this->get_constants('^ACTION_');
            $stat_mod_type          = $this->get_constants('^STATEMENT_MODULE_');

            $constants_arr          = array_merge( $constants_arr, $core_module_folder );
            $constants_arr          = array_merge( $constants_arr, $core_stat_active );
            $constants_arr          = array_merge( $constants_arr, $core_stat_pending );
            $constants_arr          = array_merge( $constants_arr, $core_stat_inactive );
            $constants_arr          = array_merge( $constants_arr, $core_stat_approved );
            $constants_arr          = array_merge( $constants_arr, $core_stat_disapp );
            $constants_arr          = array_merge( $constants_arr, $core_stat_deleted );
            $constants_arr          = array_merge( $constants_arr, $core_stat_blocked );
            $constants_arr          = array_merge( $constants_arr, $core_stat_draft );
            $constants_arr          = array_merge( $constants_arr, $core_stat_expired );
            $constants_arr          = array_merge( $constants_arr, $systems );
            $constants_arr          = array_merge( $constants_arr, $actions );
            $constants_arr          = array_merge( $constants_arr, $dpa_type );
            $constants_arr          = array_merge( $constants_arr, $media_upl_type );
            $constants_arr          = array_merge( $constants_arr, $statement_type );
            $constants_arr          = array_merge(  $constants_arr, $stat_mod_type );

            // For root path
            // Use this constant to get the correct base path or root path where the uploaded file will be stored
            $change_upload_path     = get_setting(MEDIA_SETTINGS, "change_upload_path");

            $checked_upload_path    = ( !EMPTY( $change_upload_path ) ) ? true : false;

            $constants_arr['ROOT_PATH']                             = $this->get_root_path();
            $constants_arr['CHECK_CUSTOM_UPLOAD_PATH']              = $checked_upload_path;
            $constants_arr['notification_position']                 = get_setting( GENERAL, 'notification_position' );
            /* Other parameters can be defined here. Thinking if some sys_params are applicable */
        }
        catch( PDOException $e )
        {
            $msg    = $this->get_user_message($e);

            $this->rlog_error($e);
        }
        catch( Exception $e )
        {
            $this->rlog_error($e);
        }

        echo json_encode( $constants_arr );
    }
}