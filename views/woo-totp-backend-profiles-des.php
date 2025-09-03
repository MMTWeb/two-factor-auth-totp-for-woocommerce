<table class="form-table">
	<tr>
		<th>
			<label for="woo-totp-admin-userprofile"><?php esc_html_e( 'Manage TOTP Authenticator', 'two-factor-authentication-totp-for-woocommerce' ); ?></label>
		</th>
		<td>
            <?php   
                //Main (if) condition when we are on user profile on backoffice we check if he TOTP is activated for his profile
                if(get_user_meta($porfileUserID, 'woo_totp_auth_status', true) === 'active' && !empty(get_user_meta($porfileUserID, 'woo_totp_secret_key', true))): ?>

                    <div class="woo-totp-user-profile-content">
                        <b>TOTP is activated for this account.</b>
                        <br>
                        <form method="POST">
                            <?php wp_nonce_field('woo_totp_admin_deactivate_nonce', 'woo_totp_admin_deactivate_nonce'); ?>
                            <label for="totp-disable">To disable TOTP, check the corresponding box and save the updated profile settings.</label>
                            <input id="totp-disable" type="checkbox" name="delete_totp_usermeta"> 
                            <input type="hidden" name="delete_totp_usermeta_user_id" value="<?php echo esc_attr($porfileUserID); ?>">
                        </form>
                    </div>

            <?php else: ?>
                <span>The client's account does not currently have Time-Based One-Time Password (TOTP) authentication enabled.</span>
            <?php endif; ?>
        <td>
    </tr>
</table>
   