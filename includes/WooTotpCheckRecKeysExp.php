<?php 

add_action('init','plainRecoveryKeysExpCheck');

function plainRecoveryKeysExpCheck()
{
    $userID = get_current_user_id();
    $data   = get_user_meta($userID, 'woo_totp_recovery_keys_plain', true);

    if($data){

        $expiresAT = $data['created'] + $data['duration'];

        if(time() > $expiresAT){
            delete_user_meta($userID, 'woo_totp_recovery_keys_plain');
            $data = false;
        }
    }
}

?>
