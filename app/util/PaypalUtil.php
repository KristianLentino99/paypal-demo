<?php
namespace App\util;


use App\util\classes\PaypalIntents;
use App\util\classes\PaypalLinkTypes;
use App\util\classes\PaypalOrderStatus;
use App\util\classes\PaypalStatusCodes;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Orders\OrdersAuthorizeRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalCheckoutSdk\Payments\AuthorizationsCaptureRequest;
use PayPalCheckoutSdk\Payments\AuthorizationsGetRequest;
use PayPalHttp\HttpException;
use PayPalHttp\HttpRequest;
use PayPalHttp\HttpResponse;
use Symfony\Component\VarDumper\VarDumper;

class PaypalUtil implements PaypalStatusCodes,PaypalLinkTypes,PaypalOrderStatus
{
    const STATUS_ORDER_CREATED = 'CREATED';
    protected PayPalHttpClient $client;
    protected HttpResponse $orderResponse;
    protected HttpResponse $authorizedOrder;
    protected HttpResponse $authorization;
    public function __construct()
    {
        $this->client = PayPalClient::client();
    }


    public function createOrder(array $body): HttpResponse
    {
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = $body;
        // 3. Call PayPal to set up a transaction
        $response = $this->executeRequest($request);

        $this->orderResponse = $response;

        return $this->orderResponse;
    }


    /**
     * @param HttpRequest $request
     * @return false|HttpResponse
     */
    private function executeRequest(HttpRequest $request)
    {
        try {
            return $this->client->execute($request);
        } catch (HttpException $paypalHttpException){

            return false;
        }
    }

    /**
     * return true if the order has been created correctly
     * @return bool
     * */
    public function canGoToPaypal(): bool
    {
        return $this->orderResponse->statusCode === self::STATUS_CODE_CREATED
            && !empty($this->orderResponse->result->id)
            && $this->orderResponse->result->status == self::STATUS_ORDER_CREATED;
    }

    /**
     * this function will redirect the user to the approve paypal's page
     * */
    public function goToPaypal()
    {
        $approvalUrl = self::getArrayElementByAttributeValue($this->orderResponse->result->links,'rel',self::LINK_APPROVAL);

        if($approvalUrl){

            return $approvalUrl->href;
        }
    }

    /**
     * @param string $token
     * @return HttpResponse|bool
     */
    public function retrieveAuthorizedPayment(string $token)
    {
        $authorizedPayment = new OrdersGetRequest($token);
        $response = $this->executeRequest($authorizedPayment);

        if(!empty($response->result)){
            //i'm going to authorize the order
            $authorizePayment = new OrdersAuthorizeRequest($response->result->id);

            $response = $this->executeRequest($authorizePayment);

            if(!empty($response)){
                $this->authorization = $response;
            }

            return $response;
        }

        return false;

    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return !empty($this->authorization)
            && $this->authorization->result->status === PaypalOrderStatus::ORDER_STATUS_COMPLETED;
    }

    /**
     * this function complete the process for a payment with approval url
     */
    public function finalizeOrder(): HttpResponse
    {

        $requestCapture = new AuthorizationsCaptureRequest($this->getAuthorizationId());

        return $this->executeRequest($requestCapture);
    }

    /**
     * @param $array the array to search in
     * @param string $attributeName the name of the attribute
     * @param string $attributeValue the value of the attribute
     * @return mixed return the single array item that contain the attributeValue for the column attributeName
     */
    private static function getArrayElementByAttributeValue($array, string $attributeName, string $attributeValue)
    {
        $i = array_search($attributeValue,array_column($array,$attributeName));

        return $i !== false ? $array[$i] : null;
    }

    /**
     * @return int|bool
     */
    protected function getAuthorizationId()
    {
        return empty($this->authorization) ? false
            : $this->authorization->result->purchase_units[0]->payments->authorizations[0]->id;
    }

    /**
     * @param string $orderId
     * @return false|HttpResponse
     */
    public function getOrder(string $orderId)
    {
        $orderGetRequest = new OrdersGetRequest($orderId);

        return $this->executeRequest($orderGetRequest);
    }


}
