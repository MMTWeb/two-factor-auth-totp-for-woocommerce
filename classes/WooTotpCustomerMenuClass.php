<?php 
/**
 * Implement a TOTP menu within the user’s WooCommerce account.
 * @package WOO-TOTP-AUTH
 */

namespace WooTotpAuth\Classes;

class WooTotpCustomerMenuClass
{
    public function __construct() 
    {
        add_action( 'init', [$this, 'wooTotpCustomerEndPoint']);
        add_filter( 'woocommerce_account_menu_items', [$this, 'wooTotpCustomerMenu']);
    }

    /** Add /woo-totp-menu endpoint. */
    public function wooTotpCustomerEndPoint()
    {
        add_rewrite_endpoint('woo-totp-menu', EP_PAGES);

        // Flush rewrite rules once after activation
        if(get_transient( 'woo_totp_flush_rewrites' )){
            flush_rewrite_rules();
            delete_transient( 'woo_totp_flush_rewrites' );
        }
    }

    /** Add Totp menu to Woocommerce user Account before logout url. */
    public function wooTotpCustomerMenu($menu_links)
    {
        $user = wp_get_current_user();

        if(array_intersect(['customer', 'subscriber'], (array) $user->roles )){

            $logout = $menu_links['customer-logout'];
            unset($menu_links['customer-logout']);

            $menu_links['woo-totp-menu']   = __( 'Two-Factor Authentication', 'two-factor-authentication-totp-for-woocommerce' );
            $menu_links['customer-logout']  = $logout;

            return $menu_links;

        }else{

            return $menu_links;
            
        }
    }

}

?>