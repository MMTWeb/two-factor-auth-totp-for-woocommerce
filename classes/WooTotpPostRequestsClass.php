<?php 
/**
 * This class is responsible for handling all plugin forms.
 * @package WP-WOO-AUTH
 */

namespace WooTotpAuth\Classes;

class WooTotpPostRequestsClass
{

    private $userID;
    
    /**
     * Constructs the TotpPostRequestsClass.
	*/
    public function __construct() 
    {
        add_action('init', [$this, 'currentUserID']);

        /** TOTP Verification callback.*/
        add_action( 'admin_post_woo_totp_activate', [$this, 'wooTotpActivate']);

        /** TOTP Deactivation callback.*/
        add_action( 'admin_post_woo_totp_deactivate', [$this, 'wooTotpDeactivate']);

        /** TOTP Login Verification AJAX callback.*/
        add_action( 'wp_ajax_nopriv_woo_totp_login_verify',  [$this, 'wooTotpLoginVerify'] );
        add_action( 'wp_ajax_woo_totp_login_verify', [$this,'wooTotpLoginVerify'] );

        /** TOTP deactivation callback via profiles can only be initiated through administrator.*/
        add_action( 'profile_update', [$this, 'totpAdminDeactivate'], 10, 2 );

        /** TOTP recovery codes trigger a callback. */
        add_action('admin_post_gen_new_recovery_keys',[$this,'genNewRecoveryKeys']);

        /** TOTP save settings callback. */
        add_action('admin_post_woo_totp_settings_save',[$this,'saveTotpSettings']);
    }
    
    /** Retrieve current user's ID from the session */
    public function currentUserID()
    {
        $this->userID = get_current_user_id();
    }
    
    public function wooTotpActivate()
    {
        $nonceField     = 'woo_totp_activate_nonce';
        $nonce_value    = isset( $_POST[ $nonceField ] ) ? sanitize_text_field( wp_unslash( $_POST[ $nonceField ] ) ) : '';

        if( ! wp_verify_nonce( $nonce_value, $nonceField ) ) {
            wp_die('Invalid nonce');
        }

        if(!empty($_POST['origin']) && $_POST['origin'] === 'woo_totp_backend'){
            $redirectURL    = admin_url( 'admin.php?page=woo_totp_activation' );
        }else{
            $redirectURL    = wc_get_page_permalink( 'myaccount' ) . 'woo-totp-menu';
        }

        if(!empty(get_user_meta($this->userID, 'woo_totp_activation_secret', true))){

            if(!empty($_POST['activation-code'])){
                $activationCode = sanitize_text_field(wp_unslash($_POST['activation-code']));
            }else{
                echo 'activation code field missing';
                exit;
            }
                
            $activationSecret       = get_user_meta($this->userID, 'woo_totp_activation_secret', true);
            $totpVerifyActivation   = \WooTotpAuth\Classes\WooTotpHelperClass::totpCodeVerification($activationSecret,$activationCode);

            if($totpVerifyActivation){

                if(update_user_meta($this->userID, 'woo_totp_auth_status', 'active') && update_user_meta($this->userID, 'woo_totp_secret_key', $activationSecret) ){

                    delete_user_meta($this->userID,'woo_totp_activation_secret');
                    $recoveryCodes = \WooTotpAuth\Classes\WooTotpHelperClass::generateBackupCodes($this->userID);
                    update_user_meta($this->userID, 'woo_totp_recovery_keys_plain', ['recovery_codes'=>$recoveryCodes, 'created'=> time(), 'duration'=> 5]);
                
                    wp_safe_redirect(add_query_arg(array( 'woo_totp_activation' => 'success', '_totp_notice_nonce' => wp_create_nonce( 'totp_act_des_account_notice')),  $redirectURL));
                    exit;

                }else{
                    wp_safe_redirect(add_query_arg(array( 'woo_totp_activation' => 'failed', '_totp_notice_nonce' => wp_create_nonce( 'totp_act_des_account_notice')),  $redirectURL));
                    exit;
                }

            }else{
                wp_safe_redirect(add_query_arg(array( 'woo_totp_activation' => 'failed', '_totp_notice_nonce' => wp_create_nonce( 'totp_act_des_account_notice')),  $redirectURL));
                exit;
            }
        }
    }

    public function wooTotpDeactivate()
    {
        $nonceField    = 'woo_totp_deactivate_nonce';
        $nonce_value   = isset( $_POST[ $nonceField ] ) ? sanitize_text_field( wp_unslash( $_POST[ $nonceField ] ) ) : '';

        if( ! wp_verify_nonce( $nonce_value, $nonceField ) ) {
            wp_die('Invalid nonce');
        }

        if(!empty($_POST['origin']) && $_POST['origin'] === 'woo_totp_backend'){
            $redirectURL    = admin_url( 'admin.php?page=woo_totp_activation' );
        }else{
            $redirectURL    = wc_get_page_permalink( 'myaccount' ) . 'woo-totp-menu';
        }

        if(!empty($_POST['deactivation-code'])){

            $verificationCode       = sanitize_text_field(wp_unslash($_POST['deactivation-code']));
            $userSecretKey          = get_user_meta($this->userID,'woo_totp_secret_key',true);
            $totpVerifyActivation   = \WooTotpAuth\Classes\WooTotpHelperClass::totpCodeVerification($userSecretKey,$verificationCode);

            if($totpVerifyActivation){

                if(delete_user_meta($this->userID, 'woo_totp_auth_status') && delete_user_meta($this->userID, 'woo_totp_secret_key') && delete_user_meta($this->userID, 'woo_totp_recovery_keys')){

                    wp_safe_redirect(add_query_arg(array( 'woo_totp_deactivation' => 'success', '_totp_notice_nonce' => wp_create_nonce( 'totp_act_des_account_notice')),  $redirectURL));
                    exit;

                }else{
                    wp_safe_redirect(add_query_arg(array( 'woo_totp_deactivation' => 'failed', '_totp_notice_nonce' => wp_create_nonce( 'totp_act_des_account_notice')),  $redirectURL));
                    exit;
                }

            }else{
                wp_safe_redirect(add_query_arg(array( 'woo_totp_deactivation' => 'failed', '_totp_notice_nonce' => wp_create_nonce( 'totp_act_des_account_notice')),  $redirectURL));
                exit;
            }
        }
    }

    public function wooTotpLoginVerify()
    {
        if(!empty($_COOKIE['pending_totp'])){

            $nonceField     = 'woo_totp_login_verify_nonce';
            $nonce_value    = isset( $_POST[ $nonceField ] ) ? sanitize_text_field( wp_unslash( $_POST[ $nonceField ] ) ) : '';

            if( ! wp_verify_nonce( $nonce_value, $nonceField ) ) {
                wp_send_json([
                    'success' => false,
                    'message' => 'Invalid request (nonce failed).'
                ]);
            }

            $token      = sanitize_text_field(wp_unslash($_COOKIE['pending_totp']));
            $pending    = get_transient('pending_totp_' . $token);

            if($pending['value'] && !empty($_POST['totp-login-check-code'])){

                $userID           = $pending['value'];
                $userDatas        = get_userdata($userID);
                $userSecretKey    = get_user_meta($userID, 'woo_totp_secret_key', true);
                $verificationCode = sanitize_text_field(wp_unslash($_POST['totp-login-check-code']));

                if($userSecretKey){

                    $totpLoginVerify = \WooTotpAuth\Classes\WooTotpHelperClass::totpCodeVerification($userSecretKey, $verificationCode);

                    if($totpLoginVerify){

                        if($userDatas){

                            \WooTotpAuth\Classes\WooTotpHelperClass::setUserSession($userDatas);
                            \WooTotpAuth\Classes\WooTotpHelperClass::unsetPendingCookie('pending_totp', 'pending_totp_');

                            if(current_user_can('read')){

                                wp_send_json([
                                    'success'  => true,
                                    'redirect' => admin_url()
                                ]);

                            }else{

                                wp_send_json([
                                    'success'  => true,
                                    'redirect' => get_permalink(wc_get_page_id('myaccount'))
                                ]);

                            }

                        }else{
                            wp_send_json(['success' => false, 'message' => 'Invalid user ID.']);
                        }

                    }else{
                        wp_send_json(['failed' => false, 'message' => 'Invalid verification code.']);
                    }
                }

            }elseif($pending['value'] && !empty($_POST['totp-login-check-bk-code'])){
        
                $userID     = $pending['value'];
                $userDatas  = get_userdata($userID);
                $hashedCode = sanitize_text_field(wp_unslash($_POST['totp-login-check-bk-code']));

                $totpLoginVerifyBkCode = \WooTotpAuth\Classes\WooTotpHelperClass::verifyBackupCodes($userID, $hashedCode);

                if($totpLoginVerifyBkCode){

                    if($userDatas){

                        \WooTotpAuth\Classes\WooTotpHelperClass::setUserSession($userDatas);
                        \WooTotpAuth\Classes\WooTotpHelperClass::unsetPendingCookie('pending_totp', 'pending_totp_');

                        if(current_user_can('read')){

                            wp_send_json([
                                'success'  => true,
                                'redirect' => admin_url()
                            ]);

                        }else{

                            wp_send_json([
                                'success'  => true,
                                'redirect' => get_permalink(wc_get_page_id('myaccount'))
                            ]);

                        }

                    }else{
                        wp_send_json(['success' => false, 'message' => 'Invalid user ID.']);
                    }

                }else{
                    wp_send_json(['success' => false, 'message' => 'Invalid recovery code.']);
                }

            }

        }else{

            $myAccount = wc_get_page_permalink('myaccount');
            
            wp_send_json([
                'expired' => true,
                'message' => 'The verification time has expired. <a href="'.$myAccount.'">Please login and try again.</a>'
            ]);
        }
    }

    public function totpAdminDeactivate($user_id, $old_user_data)
    {

        $nonceField    = 'woo_totp_admin_deactivate_nonce';
        $nonce_value   = isset( $_POST[ $nonceField ] ) ? sanitize_text_field( wp_unslash( $_POST[ $nonceField ] ) ) : '';

        if(!wp_verify_nonce( $nonce_value, $nonceField)){
            wp_die('Invalid nonce');
        }

        if(!empty($_POST['delete_totp_usermeta_user_id'])){
            delete_user_meta($user_id, 'woo_totp_auth_status');
            delete_user_meta($user_id, 'woo_totp_secret_key');
            delete_user_meta($user_id, 'woo_totp_recovery_keys');
        }

    }

    public function genNewRecoveryKeys()
    {
        $nonceField     = 'gen_new_recovery_keys_nonce';
        $nonce_value    = isset( $_POST[ $nonceField ] ) ? sanitize_text_field( wp_unslash( $_POST[ $nonceField ] ) ) : '';

        if( ! wp_verify_nonce( $nonce_value, $nonceField ) ) {
            wp_die('Invalid nonce');
        }

        $redirectURL    = wp_get_referer() ?: home_url();

        $recoveryCodes = \WooTotpAuth\Classes\WooTotpHelperClass::generateBackupCodes($this->userID);
        update_user_meta($this->userID, 'woo_totp_recovery_keys_plain', ['recovery_codes'=>$recoveryCodes, 'created'=> time(), 'duration'=> 5]);

        wp_safe_redirect($redirectURL);
        exit;
    }

    public function saveTotpSettings()
    {
        $nonceField = 'woo_totp_settings_save_nonce';
        $nonce_value    = isset( $_POST[ $nonceField ] ) ? sanitize_text_field( wp_unslash( $_POST[ $nonceField ] ) ) : '';

        if( ! wp_verify_nonce( $nonce_value, $nonceField ) ) {
            wp_die('Invalid nonce');
        }

        if(!current_user_can('administrator')){
            wp_die('Available exclusively to the administrator');
            exit;
        }

        if(!empty($_POST['woo_totp_session_duration']) && !empty($_POST['woo_totp_admin_email'])){

            $duration   =  sanitize_text_field(intval(($_POST['woo_totp_session_duration'])));
            $adminEmail = sanitize_email(wp_unslash($_POST['woo_totp_admin_email']));

            if($duration < 30 || $duration > 86400){
                wp_safe_redirect(add_query_arg(array( 'settings_failed' => 'duration', '_totp_notice_nonce' => wp_create_nonce( 'totp_settings_notice')), admin_url('admin.php?page=woo_totp_settings')));
                exit;
            }

            if(!is_email($adminEmail)){
                wp_safe_redirect(add_query_arg(array( 'settings_failed' => 'admin_email', '_totp_notice_nonce' => wp_create_nonce( 'totp_settings_notice')), admin_url('admin.php?page=woo_totp_settings')));
                exit;
            }

            update_option('_woo_totp_session_duration', $duration);
            update_option('_woo_totp_admin_email', $adminEmail);
            
            wp_safe_redirect(add_query_arg(array('settings_success' => 'true', '_totp_notice_nonce' => wp_create_nonce( 'totp_settings_notice')), admin_url('admin.php?page=woo_totp_settings')));
            exit;

        }
    }

}