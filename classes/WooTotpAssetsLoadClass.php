<?php 
/**
 * Loads all necessary assets.
 * @package WOO-TOTP-AUTH
 */

namespace WooTotpAuth\Classes;

class WooTotpAssetsLoadClass
{
    public function __construct() 
    {
        /** CSS frontend */
        add_action('wp_enqueue_scripts', [$this, 'wooTotpFrontAssets']);
        /** CSS backend */
        add_action('admin_enqueue_scripts', [$this, 'wooTotpAdminAssets']);
    }

    public function wooTotpFrontAssets()
    {
        wp_register_style( 'woo-totp-front-stylesheet', plugin_dir_url(__DIR__ . '../' ) . 'assets/css/woo-totp-frontend-style.css', array(), filemtime( plugin_dir_path( __DIR__ . '../' ) . 'assets/css/woo-totp-frontend-style.css' ));
        wp_enqueue_style( 'woo-totp-front-stylesheet' );

        wp_enqueue_script('woo-totp-js', plugin_dir_url( __DIR__ . '../' ).'assets/js/main.js', array('jquery'), '20120206', true );
        wp_enqueue_script('woo-totp-js');

        wp_register_script('woo-totp-login', plugin_dir_url( __DIR__ . '../' ).'assets/js/woo-totp-login.ajax.js', array('jquery'), '20120206', true );
        wp_enqueue_script('woo-totp-login');
        wp_localize_script('woo-totp-login', 'WPURLS', array( 'siteurl' => get_option('siteurl') ));
    }
    
    /** Admin backend stylesheet */
    public function wooTotpAdminAssets()
    {
        wp_register_style( 'woo-totp-admin-stylesheet', plugin_dir_url(__DIR__ . '../' ) . 'assets/css/woo-totp-backend-style.css', array(), filemtime( plugin_dir_path( __DIR__ . '../' ) . 'assets/css/woo-totp-backend-style.css' ));
        wp_enqueue_style( 'woo-totp-admin-stylesheet' );

        wp_register_script('woo-totp-admin-js', plugin_dir_url( __DIR__ . '../' ).'assets/js/main-backend.js', array('jquery'), '20120206', true );
        wp_enqueue_script('woo-totp-admin-js');
    }

}