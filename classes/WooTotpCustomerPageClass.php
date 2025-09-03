<?php 
/**
 * Add a page callback to the TOTP menu within the WooCommerce user account.
 * @package WP-WOO-AUTH
 */

namespace WooTotpAuth\Classes;

class WooTotpCustomerPageClass extends WooTotpBaseClass
{
    public function __construct() 
    {
        add_action('woocommerce_account_woo-totp-menu_endpoint', [$this, 'wooTotpCustomerPage']);
    }

    /** TOTP activation/deactivation page callback. */
    public function wooTotpCustomerPage() 
    {   
         $userID    = get_current_user_id();
         $user      = wp_get_current_user();

        if(array_intersect(['customer', 'subscriber'], (array) $user->roles )){

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
                $actSecret    = $secret;

                $data = compact('qrCode','actSecret');
                $this->renderView('woo-totp-customer-account', $data);

            }else{

                $qrCode = null;
                $data = compact('qrCode');
                $this->renderView('woo-totp-customer-account', $data);

            }
        
        }else{

            echo 'This is available only for customers and subscribers. You can manage your TOTP settings from your backend interface.';

        }

    }
    
}