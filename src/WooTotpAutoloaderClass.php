<?php
/**
 * Load all plugin classes during this loader initialization within the main plugin file.
 * @package WOO-TOTP-AUTH
 */

namespace WooTotpAuth\Src;

class WooTotpAutoloaderClass
{

    public static function init()
    {

        new \WooTotpAuth\Classes\WooTotpAssetsLoadClass();
        new \WooTotpAuth\Classes\WooTotpPostRequestsClass();
        new \WooTotpAuth\Classes\WooTotpLoginClass();
        new \WooTotpAuth\Classes\WooTotpAdminClass();
        new \WooTotpAuth\Classes\WooTotpCustomerMenuClass();
        new \WooTotpAuth\Classes\WooTotpCustomerPageClass();

    }

}