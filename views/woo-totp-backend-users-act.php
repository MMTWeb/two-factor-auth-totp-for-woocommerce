<div class="wrap">
    <!-- Page title like WP core -->
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Woo TOTP Auth Activate', 'two-factor-authentication-totp-for-woocommerce' ); ?></h1>
        
    <hr class="wp-header-end">

        <?php if(!empty($_GET['woo_totp_activation']) && $_GET['woo_totp_activation'] === 'success' && !empty($_GET['_totp_notice_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_totp_notice_nonce'])), 'totp_act_des_account_notice')): ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>Success!</strong> Your TOTP activation were saved successfully.</p>
            </div>
         <?php elseif(!empty($_GET['woo_totp_activation']) && $_GET['woo_totp_activation'] === 'failed' && !empty($_GET['_totp_notice_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_totp_notice_nonce'])), 'totp_act_des_account_notice')): ?>
                <div class="notice notice-error is-dismissible">
                    <p><strong>Error!</strong> Activation failed, please check your activation code from the APP and try again.</p>
                </div>
        <?php elseif(!empty($_GET['woo_totp_deactivation']) && $_GET['woo_totp_deactivation'] === 'success' && !empty($_GET['_totp_notice_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_totp_notice_nonce'])), 'totp_act_des_account_notice')): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>Success!</strong> Your TOTP deactivation were saved successfully.</p>
                </div>
         <?php elseif(!empty($_GET['woo_totp_deactivation']) && $_GET['woo_totp_deactivation'] === 'failed' && !empty($_GET['_totp_notice_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_totp_notice_nonce'])), 'totp_act_des_account_notice')): ?>
                <div class="notice notice-error is-dismissible">
                    <p><strong>Error!</strong> Deactivation failed, please check your activation code from the APP and try again.</p>
                </div>
        <?php endif; ?>

        <div class="card woo-totp-backend-act-card">
                                
        <?php
            $userID = get_current_user_id();
            if($qrCode):  
        ?>
                <p>Activate TOTP by scanning the QR code with your TOTP authentication app (e.g., Google Authenticator, Authy). This securely adds your account to the app, providing an extra layer of protection against unauthorized access.</p>

                <div class="woo-totp-backend-act-container">
                    <div>
                        <?php echo '<img src="' . esc_html($qrCode) . '" alt="TOTP QR Code" />'; ?>
                    </div>

                    <div class="woo-totp-backend-act-content">
                        <span><b>If you can't scan, enter this key manually: </b> <br> <?php echo esc_html($userDatas['secret']); ?></span>
                        <form method="POST" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
                            <?php wp_nonce_field('woo_totp_activate_nonce', 'woo_totp_activate_nonce'); ?>
                            <input type="hidden" name="action" value="woo_totp_activate">
                            <input type="hidden" name="origin" value="woo_totp_backend">
                            <input type="text" name="activation-code" placeholder="<?php echo esc_html__('Enter 6-digit code', 'two-factor-authentication-totp-for-woocommerce'); ?>" pattern="\d{6}" maxlength="6" class="verification-code-form" required autofocus>
                            <button type="submit" class="verification-code-form button button-primary"><?php echo esc_html__('Activate', 'two-factor-authentication-totp-for-woocommerce'); ?></button>
                        </form>
                    </div>
                </div>

        <?php 
            endif;
        ?>

        <?php 
            
            if(!empty(get_user_meta($userID, 'woo_totp_auth_status', true)) && get_user_meta($userID, 'woo_totp_auth_status', true) == 'active' ): ?>
                
                <h4>TOTP Auth deactivation</h4>
                <p>TOTP is already activated on your account. To deactivate it, please enter the verification code displayed in your TOTP authentication app.</p>

                <div class="woo-totp-deactivation">
                    <form method="POST" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
                        <?php wp_nonce_field('woo_totp_deactivate_nonce', 'woo_totp_deactivate_nonce'); ?>
                        <input type="hidden" name="action" value="woo_totp_deactivate">
                        <input type="hidden" name="origin" value="woo_totp_backend">
                        <div>
                            <input type="text" name="deactivation-code" pattern="\d{6}" maxlength="6" placeholder="<?php echo esc_html__('Enter 6-digit code', 'two-factor-authentication-totp-for-woocommerce'); ?>" class="verification-code-form" required autofocus>
                            <button type="submit" class="verification-code-form deactivate-button button button-primary"><?php echo esc_html__('Deactivate', 'two-factor-authentication-totp-for-woocommerce'); ?></button>
                        </div>
                    </form>
                </div>

            <?php 

                if(!empty(get_user_meta($userID, 'woo_totp_recovery_keys_plain',true))): 

                    $recoveryCodes = get_user_meta($userID, 'woo_totp_recovery_keys_plain',true);
            ?>

                    <div class="woo-totp-recovery-codes-wrap">
                        <h4>Recovery Codes</h4>
                        <p>Please save these recovery keys in a safe place. Each code can only be used once.</p>

                        <ul id="admin-recovery-codes-list" style="position:relative;">
                            <button id="copy-recovery-codes" class="button">📋 Copy</button>
                            <?php 
                                foreach($recoveryCodes['recovery_codes']  as $key => $recoveryCode): 
                                    echo '<li style="list-style:none; padding:2px 0;">'.esc_html($recoveryCode).'</li>';
                                endforeach;  
                            ?> 
                        </ul> 
                    </div> 
            <?php  
                endif;
            ?>

            <?php 
                if(get_user_meta($userID, 'woo_totp_recovery_keys',true)): 
            ?>
                    <div class="woo-totp-gen-new-bk-codes">
                        <h4> Generate new recovery keys </h4>
                        <form method="POST" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" onsubmit="return confirm('Are you sure you want to generate new recovery keys? Your old codes will stop working.');">
                            <?php wp_nonce_field('gen_new_recovery_keys_nonce', 'gen_new_recovery_keys_nonce'); ?>
                            <input type="hidden" name="action" value="gen_new_recovery_keys">
                            <button type="submit" class="generate-new-bk-codes button button-primary" name="gen_new_bk_codes"><?php echo esc_html__('Recovery keys', 'two-factor-authentication-totp-for-woocommerce'); ?></button>
                        </form>
                    </div>

                <?php   
            
                endif; 

            endif;  
    ?>
    </div>
</div>