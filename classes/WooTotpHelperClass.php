<?php 
/**
 *  Plugin Helper
 * @package WOO-TOTP-AUTH
 */

namespace WooTotpAuth\Classes;

use Otp\Otp;
use ParagonIE\ConstantTime\Encoding;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class WooTotpHelperClass
{

    /** We utilize cookies to store the user’s ID, which is necessary for OTP validation **/
    public static function wooTotpSetCookies($data, $tokenName, $cookieName, $duration = null)
    {
        
        if(empty($duration)){
            $duration = 0;
        }

        $token = wp_generate_password(32, false);

        // Store mapping in a transient (server-side)
        set_transient($tokenName . $token, ['value' => $data], $duration);

        //Set cookie in browser
        setcookie(
            $cookieName,
            $token, 
            time() + $duration,
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );
    }

    public static function unsetPendingCookie($cookieName, $tokenName)
    {
         if(!empty($_COOKIE[$cookieName])){
            $token = sanitize_text_field(wp_unslash($_COOKIE[$cookieName]));
            delete_transient($tokenName . $token);
            setcookie('pending_totp', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);
        }
    }

    /** Store the user’s session after successful TOTP verification. */
    public static function setUserSession($userDatas)
    {
        clean_user_cache($userDatas->ID);
        wp_clear_auth_cookie();
        wp_set_current_user($userDatas->ID);
        wp_set_auth_cookie($userDatas->ID, true, is_ssl());
        update_user_caches($userDatas);
    }

    /** Generate a TOTP secret key using the OTP library. **/
    public static function totpGenerateSecret()
    {
        $secret = \Otp\GoogleAuthenticator::generateRandom();
        return $secret;
    }

    /** The chillerlan app will return a QR code to enable the user to add TOTP authentication within their app. **/
    public static function totpQrCode($secret,$issuer,$user) 
    {
        $secret = $secret;
        $issuer = $issuer;
        $user   = $user; 
        $url    = "otpauth://totp/{$issuer}:{$user}?secret={$secret}&issuer={$issuer}";

        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'   => QRCode::ECC_L,
            'scale'      => 5,
        ]);

        $base64 = (new QRCode($options))->render($url);
        return $base64;
    }

    /** Checking the integrity of the private key. **/
    public static function totpCodeVerification($secret,$verificationCode)
    {   
        $otp = new Otp();

        if($otp->checkTotp(Encoding::base32DecodeUpper($secret), $verificationCode)) {
            return true;
        }else{
            return false;
        }
    }

    /** Counts the number of users who have set up TOTP authentication. **/
    public static function countEnabledTOTP()
    {
        $args = ['count_total' => true, 'fields' => 'ID' ];
   
        $args['meta_query'] = [
            [
                'key'     => 'woo_totp_auth_status',
                'value'   => 'active',
                'compare' => '='
            ]
        ];

        $query = new \WP_User_Query($args);
        return $query->get_total();
    }
    
    /** Generate 6 recovery keys **/
    public static function generateBackupCodes($userID)
    {
        $count  = 6;
        $length = 12;
        $codes  = [];

        for($i=0; $i < $count; $i++){
            $codes[] = bin2hex(random_bytes($length / 2)); // 12-char hex
        }

        $hashedCodes = array_map( function($code){
            return password_hash( $code, PASSWORD_DEFAULT );
        }, $codes );

        update_user_meta($userID, 'woo_totp_recovery_keys', wp_json_encode($hashedCodes));

        return $codes;
    }

    /** Validate recovery key and consume if valid. */
    public static function verifyBackupCodes($userID, $hashedCode)
    {
        $storedCodes = get_user_meta( $userID, 'woo_totp_recovery_keys', true );

        if(empty($storedCodes)){
            return false;
        }

        $codes = json_decode($storedCodes, true);

        foreach($codes as $i => $hash ){
            if(password_verify($hashedCode, $hash)){
                unset( $codes[ $i ] );
                update_user_meta($userID, 'woo_totp_recovery_keys', wp_json_encode(array_values($codes)));
                return true;
            }
        }

        return false;
    }

}