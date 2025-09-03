<?php
/**
 * The TotpBaseController is responsible for loading the necessary views associated with the WordPress and WooCommerce menu page callbacks.
 * @package WOO-TOTP-AUTH
 */

namespace WooTotpAuth\Classes;

class WooTotpBaseClass
{
    /**
     * The $data argument is an array and can be null.
     * You can pass values from the compact() function to be used within the page callback view.
	*/
    protected function renderView($viewName, $data = null ) 
    {
        if(!empty($data) && (array) $data ){
            extract($data);
        }
        
        //All page callback views are stored in the views directory
        $viewPath = plugin_dir_path(__DIR__) . 'views/' . $viewName . '.php';

        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo '<p>View ' . esc_html( $viewName ) . ' not found.</p>';
        }
    }
}

?>