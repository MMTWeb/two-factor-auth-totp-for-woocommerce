<?php 
/**
 * Remove the TOTP verification page title callback.
 * @package WP-WOO-TOTP
 */

add_filter('the_title','hideWooTotpPageTitle', 10, 2 );

function hideWooTotpPageTitle($title, $post_id )
{
    if(is_page('woo-totp-login-page') && in_the_loop() && !is_admin()){
        return ''; 
    }
    return $title;
}


?>