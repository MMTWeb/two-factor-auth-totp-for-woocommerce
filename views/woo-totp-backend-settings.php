<div class="wrap">
    <h1>Woo TOTP Auth Settings</h1>

    <?php if(!empty($_GET['settings_failed']) && $_GET['settings_failed'] === 'duration' && wp_verify_nonce(!empty($_GET['_totp_notice_nonce']), 'totp_settings_notice')): ?>
        <div class="notice notice-error is-dismissible">
            <p><strong>Error!</strong> Session duration must be between 30 seconds and 86400 seconds (24h).</p>
        </div>
    <?php elseif(!empty($_GET['settings_failed']) && $_GET['settings_failed'] === 'admin_email' && wp_verify_nonce(!empty($_GET['_totp_notice_nonce']), 'totp_settings_notice')):  ?>
        <div class="notice notice-error is-dismissible">
            <p><strong>Error!</strong> Please enter a valid admin email address.</p>
        </div>
    <?php elseif(!empty($_GET['settings_success']) && wp_verify_nonce(!empty($_GET['_totp_notice_nonce']), 'totp_settings_notice')): ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>Success!</strong>Settings saved successfully.</p>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
        <?php wp_nonce_field('woo_totp_settings_save_nonce', 'woo_totp_settings_save_nonce'); ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="woo_totp_session_duration">TOTP Session Duration</label></th>
                <td>
                    <input name="woo_totp_session_duration" type="number" id="woo_totp_session_duration" value="<?php echo esc_attr(get_option('_woo_totp_session_duration', 60)); ?>"class="small-text"> seconds
                    <p class="description">The TOTP session remains valid for [X] seconds. <b> The default value is 60 seconds, but you can accept values between 30 and 86400 seconds (24 hours).</b></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="woo_totp_admin_email">Support Email</label></th>
                <td>
                    <input name="woo_totp_admin_email" type="email" id="woo_totp_admin_email" value="<?php echo esc_attr(get_option('_woo_totp_admin_email', get_option('admin_email'))); ?>" class="regular-text">
                    <p class="description">This email address will be displayed to users for support issues. <b> By default, it's the email address of the website's main administrator.</b></p>
                </td>
            </tr>
        </table>
        <input type="hidden" name="action" value="woo_totp_settings_save">
        <?php submit_button(); ?>
    </form>
</div>