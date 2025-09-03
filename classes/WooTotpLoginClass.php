<?php 
/**
 * Maintains the user ID in the session and logs the user out, redirecting them to the TOTP verification form.
 * The default retention period for the ID on cookie is 60 seconds (adjustable in the settings). After the verification form is removed, users will need to log in again.
 * @package WP-WOO-AUTH
 */

namespace WooTotpAuth\Classes;

class WooTotpLoginClass
{

    /**
	 * Constructs the TotpLoginClass
	*/
    public function __construct() 
    {
        //Wordpress login hook
        add_filter('authenticate', [$this, 'wooTotpCheckBeforeLogin'], 30, 3);
    }

    /**
	 * Fallback to the TOTP authentication step
	*/
    public function wooTotpCheckBeforeLogin($user, $username, $password) 
    {

        if(is_a($user, 'WP_User')){

            if(get_user_meta($user->ID, 'woo_totp_auth_status', true) === 'active'){

                if(!empty(get_option('_woo_totp_session_duration'))){
                    $duration = get_option('_woo_totp_session_duration');
                }else{
                    $duration = 60;
                }

                if(empty($_COOKIE['pending_totp'])){
                    \WooTotpAuth\Classes\WooTotpHelperClass::wooTotpSetCookies($user->ID, 'pending_totp_', 'pending_totp', $duration);
                }

                // Prevent user login and force redirection to OTP validation page
                wp_safe_redirect(get_permalink(get_page_by_path('woo-totp-login-page')));
                exit;

            }
        }

        return $user;

    }

}