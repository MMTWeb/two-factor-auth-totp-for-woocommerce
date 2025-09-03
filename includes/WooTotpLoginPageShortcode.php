<?php 
/**
 * When the plugin is activated for the first time, it automatically generates a TOTP authentication page and simultaneously integrates the associated shortcode.
 * @package WP-WOO-TOTP
 */

function wooTotpLoginPage() {
    ob_start();

    if(!empty($_COOKIE['pending_totp'])){

        $token      = sanitize_text_field(wp_unslash($_COOKIE['pending_totp']));
        $pending    = get_transient('pending_totp_' . $token);
    
        if(!empty(get_option('_woo_totp_admin_email'))){
            $adminEmail = get_option('_woo_totp_admin_email');
        }else{
            $adminEmail = get_option('admin_email');
        }

?>
        <div class="woo-totp-verification-code-container">
            <h2><?php echo esc_html__('Two-Factor Authentication', 'two-factor-authentication-totp-for-woocommerce'); ?></h2>

            <div class="woo-totp-notifications"></div>

            <form method="POST" id="woo-totp-verification-form">
                <?php wp_nonce_field('woo_totp_login_verify_nonce', 'woo_totp_login_verify_nonce'); ?>
                <input type="hidden" name="action" value="woo_totp_login_verify">

                <input type="text" name="totp-login-check-code" class="totp-login-check-code"  inputmode="numeric" pattern="\d{6}" maxlength="6" placeholder="<?php echo esc_html__('Enter 6-digit code', 'two-factor-authentication-totp-for-woocommerce'); ?>" autocomplete="off" required>
                <input type="text" name="totp-login-check-bk-code" class="totp-login-bk-code" placeholder="<?php echo esc_html__('Enter your recovery code', 'two-factor-authentication-totp-for-woocommerce'); ?>" autocomplete="off" style="display:none;">
                <button type="submit"><?php echo esc_html__('Verify', 'two-factor-authentication-totp-for-woocommerce'); ?></button>
            </form>
            <div style="display:flex; gap:10px; flex-direction:column;"> 
                <p style="font-size: 13px; color: #999; text-align: center; margin-top: 15px;"> Trouble? <a href="mailto:<?php echo esc_html($adminEmail); ?>"><?php echo esc_html__('Please contact support.', 'two-factor-authentication-totp-for-woocommerce'); ?></a> </p> 
                <div> 
                    <input type="checkbox" id="recovery-code" name="recovery-code"/> 
                    <label for="recovery-code"><?php echo esc_html__('Use recovery key', 'two-factor-authentication-totp-for-woocommerce'); ?></label> 
                </div> 
            </div>
        </div>
    <?php

    }else{

        if(!is_user_logged_in()){
            $myAccount = wc_get_page_permalink('myaccount');
            echo 'The verification time has expired. Please <a href="' . esc_url( $myAccount ) . '">login</a> and try again.';
        }
    }

    return ob_get_clean();
}
add_shortcode('woo_totp_login_page', 'wooTotpLoginPage');
?>