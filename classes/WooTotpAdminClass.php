<?php 
/**
 * @package WOO-TOTP-AUTH
 */

namespace WooTotpAuth\Classes;

class WooTotpAdminClass extends WooTotpBaseClass
{

    /**
    * Constructs the TotpAdminclass
    **/
    public function __construct() 
    {
        //TOTP activation page (Setting submenu)
        add_action('admin_menu', [$this,'addTotpActivationSettingsSubmenu']);
        //Add Totp funcionality to user profile
        add_action( 'show_user_profile',  [$this, 'addTotpDeactivationUserProfile'], 10, 1 );
        add_action( 'edit_user_profile',  [$this, 'addTotpDeactivationUserProfile'], 10, 1 );
        //New TOTP column in users table
        add_filter( 'manage_users_columns', [$this,'UserTableColTotpStatus']);
        add_filter( 'manage_users_custom_column', [$this,'UserTableColTotpGetStatus'], 10, 3 );
        add_action('views_users', [$this,'UserTableColTotpFilterForm'], 10, 1);
        add_action('pre_get_users', [$this,'UserTableColTotpFilterFilterShow']);

    }

     /** TOTP activation menu for users who have role like editor... that allow them to access to the backend  */
    function addTotpActivationSettingsSubMenu() 
    {
        add_menu_page( 'Woo TOTP Auth Activate', 'Woo TOTP Auth Activate', 'read', 'woo_totp_activation', [$this,'totpAuthActivation'], 'dashicons-admin-network', 999 );
        add_submenu_page('woo_totp_activation','Woo TOTP Auth Settings','Woo TOTP Auth Settings','manage_options','woo_totp_settings',[$this,'totpAuthSettings']);
    }

    /** Return TOTP activation page for users who have role like editor... that allow them to access to the backend  */
    public function totpAuthActivation()
    {

        $userID = get_current_user_id();

        if(empty(get_user_meta($userID, 'woo_totp_auth_status', true)) && get_user_meta($userID, 'woo_totp_auth_status', true) != 'active'){

            if(!empty(get_user_meta($userID,'woo_totp_activation_secret',true))){
                $secret = get_user_meta($userID,'woo_totp_activation_secret',true);
            }else{
                $secret = \WooTotpAuth\Classes\WooTotpHelperClass::totpGenerateSecret();
                update_user_meta($userID,'woo_totp_activation_secret',$secret);
            }

            $current_user = wp_get_current_user();
            $userEmail    = $current_user->user_email;
            $issuer       = get_bloginfo('name');
            $qrCode       = \WooTotpAuth\Classes\WooTotpHelperClass::totpQrCode($secret,$issuer,$userEmail);
            $userDatas    = array('email'=>$userEmail, 'secret'=>$secret);

            $data = compact('qrCode','userDatas');
            $this->renderView('woo-totp-backend-users-act', $data);

        }else{

            $qrCode = null;
            $data = compact('qrCode');
            $this->renderView('woo-totp-backend-users-act', $data);

        }
    }

    public function totpAuthSettings()
    {
        if(current_user_can('administrator')){
            $currentUserID  = get_current_user_id();
            $data           = compact('currentUserID');
            $this->renderView('woo-totp-backend-settings', $data);
        }else{
            echo 'Available exclusively to the administrator';
        }
    }

    /** TOTP deactivation on user profile (only for admin) */
    public function addTotpDeactivationUserProfile($profile_user)
    {
        if(current_user_can('administrator')){
            $porfileUserID = $profile_user->ID;
            $data   = compact('porfileUserID');
            $this->renderView('woo-totp-backend-profiles-des', $data);
        }
    }

    /** Adds a TOTP status column to the user table */
    public function UserTableColTotpStatus($columns) 
    {
        if(current_user_can('administrator')){
            $columns['totp_status_account'] = 'TOTP Auth';
        }

        return $columns;
    }

    /** Retrieves the TOTP status for a specific user */
    public function UserTableColTotpGetStatus($output, $column_name, $user_id) 
    {
        if(current_user_can('administrator')){
            if($column_name === 'totp_status_account'){ 
                if(get_user_meta( $user_id, 'woo_totp_auth_status', true ) === 'active'){
                    return 'Enabled';
                }else{
                    return 'Disabled';
                }
                return $output;
            }
        }
    }

    /** Generates the URL for filtering users who have enabled TOTP authentication. */
    public function UserTableColTotpFilterForm($views) 
    {
        if(current_user_can('administrator')){

            $getCount   = \WooTotpAuth\Classes\WooTotpHelperClass::countEnabledTOTP();
            $current    = isset($_GET['totp_filter_param']) ? sanitize_text_field(wp_unslash($_GET['totp_filter_param'])) : '';
            $all_url    = add_query_arg('totp_filter_param', 'enabled');

            $all_url = add_query_arg(array('totp_filter_param' => 'enabled', '_totp_admin_table_filter_nonce' => wp_create_nonce( 'totp_filter_action' )));

            $current = '';

            if(isset($_GET['totp_filter_param'] ) && isset($_GET['_totp_admin_table_filter_nonce']) && wp_verify_nonce(!empty($_GET['_totp_admin_table_filter_nonce']), 'totp_filter_action' ) ){
                $current = sanitize_text_field( wp_unslash($_GET['totp_filter_param']));
            }

            $views['totp_enabled'] = sprintf( '<a href="%s"%s>%s</a>', esc_url($all_url), $current === 'enabled' ? ' class="current"' : '', __('With TOTP', 'two-factor-authentication-totp-for-woocommerce') . '(' . $getCount . ')');

        }

        return $views;

    }

    /** Filters the user table based on the enabled TOTP status */
    public function UserTableColTotpFilterFilterShow($query) 
    {

        if(!current_user_can('administrator')){
            return;
        }

        if(wp_verify_nonce(!empty($_GET['_totp_admin_table_filter_nonce']), 'totp_filter_action' ) ){
            wp_die('Invalid nonce');
        }

        if(current_user_can('administrator') && isset($_GET['totp_filter_param'])){

            $filterValue = sanitize_text_field(wp_unslash($_GET['totp_filter_param']));

            if($filterValue === 'enabled'){
                
                $query->set('meta_query', array(
                    array(
                        'key'     => 'woo_totp_auth_status',
                        'value'   => 'active',
                        'compare' => '='
                    ),
                ));

            }

        }

    }

}