<?php


namespace App\util;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;

class PayPalClient
{
    const ENVIRONMENT_TEST = 'sandbox';
    const ENVIRONMENT_PRODUCTION = 'live';
    const ENVIRONMENT = 'sandbox';
    const PAYPAL_CLIENT_ID = '{YOUR_CLIENT_ID}';
    const PAYPAL_CLIENT_SECRET = '{YOUR_CLIENT_SECRET}';


    /**
     * Returns PayPal HTTP client instance with environment that has access
     * credentials context. Use this instance to invoke PayPal APIs, provided the
     * credentials have access.
     */
    public static function client(): PayPalHttpClient
    {
        return new PayPalHttpClient(self::environment());
    }

    /**
     * Set up and return PayPal PHP SDK environment with PayPal access credentials.
     * This sample uses SandboxEnvironment. In production, use LiveEnvironment.
     */
    public static function environment()
    {
        $clientId = self::PAYPAL_CLIENT_ID;
        $clientSecret = self::PAYPAL_CLIENT_SECRET;

        if(self::ENVIRONMENT === self::ENVIRONMENT_TEST){
            return new SandboxEnvironment($clientId, $clientSecret);
        }

        return new ProductionEnvironment($clientId, $clientSecret);

    }
}
