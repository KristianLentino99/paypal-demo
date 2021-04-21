<?php

namespace App\Http\Controllers;

use App\util\classes\PaypalIntents;
use App\util\classes\PaypalOrderStatus;
use App\util\classes\PaypalStatusCodes;
use App\util\PayPalClient;
use App\util\PaypalUtil;
use App\util\paypalv1\Paypal;
use Illuminate\Http\Request;

class PaymentController extends Controller
{

    public static function getOrder()
    {
        return array(
            'intent' => PaypalIntents::PAYPAL_INTENT_AUTHORIZE,
            'application_context' =>
                array(
                    'brand_name' => 'EXAMPLE INC',
                    'locale' => 'it-IT',
                    'landing_page' => 'BILLING',
                    'shipping_preference' => 'SET_PROVIDED_ADDRESS',
                    'user_action' => 'PAY_NOW',
                ),
            'application_context' =>
                array(
                    'return_url' => url('payment/success?v=v2'),
                    'cancel_url' => url('payment/cancel?v=v2')
                ),
            'purchase_units' =>
                array(
                    0 =>
                        array(
                            'name' => 'T-Shirt',
                            'reference_id' => 'PUHF',
                            'description' => 'Test descrizione ',
                            'custom_id' => '0000003/2021',
                            'soft_descriptor' => 'Test descriptor',
                            'amount' =>
                                array(
                                    'currency_code' => 'USD',
                                    'value' => '220.00'
                                )
                        )
                ),
            'items' =>
                array(
                    0 =>
                        array(
                            'name' => 'T-Shirt',
                            'description' => 'Green XL',
                            'sku' => 'sku01',
                            'unit_amount' =>
                                array(
                                    'currency_code' => 'USD',
                                    'value' => '90.00',
                                ),
                            'tax' =>
                                array(
                                    'currency_code' => 'USD',
                                    'value' => '10.00',
                                ),
                            'quantity' => '1',
                            'category' => 'PHYSICAL_GOODS',
                        ),
                    1 =>
                        array(
                            'name' => 'Shoes',
                            'description' => 'Running, Size 10.5',
                            'sku' => 'sku02',
                            'unit_amount' =>
                                array(
                                    'currency_code' => 'USD',
                                    'value' => '45.00',
                                ),
                            'tax' =>
                                array(
                                    'currency_code' => 'USD',
                                    'value' => '5.00',
                                ),
                            'quantity' => '2',
                            'category' => 'PHYSICAL_GOODS',
                        ),
                ),
            'shipping' =>
                array(
                    'method' => 'United States Postal Service',
                    'address' =>
                        array(
                            'address_line_1' => '123 Townsend St',
                            'address_line_2' => 'Floor 6',
                            'admin_area_2' => 'San Francisco',
                            'admin_area_1' => 'CA',
                            'postal_code' => '94107',
                            'country_code' => 'US',
                        ),
                ),
        );
    }
    public function demoPayment()
    {

        return view('choose_version');
    }

    public function demoPay(Request $request)
    {

        if($request->get('v') === 'v1'){

            $paypal = new Paypal();
            $paypal->createPayer();

            /**
             * i'm setting the two possible redirect url:
             *  one for success (when user pay)
             *  one for generic failure
             *
             */
            $paypal->createRedirectUrls(
                url('payment/success?v=v1'),
                url('payment/success?v=v1')
            );

            //i'm setting the amount of the transaction
            $amount = $paypal->addAmount(100.50);

            //i'm creating the Transaction for the specified amount
            $paypal->createTransaction($amount,"Pagamento rata Ordine N° 000001/2021");

            /**
                here the util will return the approvalUrl if everything is fine
             * you have a array key "success" which contains if everything is ok
             */
            $response = $paypal->createPayment();


            if(!empty($response)){

                //i'll redirect the user to the paypal checkout page
                return redirect($response['approvalUrl']);
            }

        } else {

            $paypalUtil = new PaypalUtil();
            //i'm creating a order with a fake detail
            $paypalUtil->createOrder(self::getOrder());

            //i'm checking if the previous request is ok and has all the things that i need to go to Paypal
            if($paypalUtil->canGoToPaypal()){

                return redirect($paypalUtil->goToPaypal());
            }
        }

    }

    public function successPayment(Request $request)
    {

        if($request->get('v') === 'v1'){

            $paypal = new Paypal();
            //i retrieve the payment from the id
            $paypal->retrievePaymentById($request->get('paymentId'),$request->get('PayerID'));

            // i'm going to pay
            $response = $paypal->pay();

            if($response->state === Paypal::PAYMENT_STATUS['APPROVED']){

                return view('welcome',[
                    'title' => 'Paid succesfully!'
                ]);

            }


        } else {

            $paypalUtil = new PaypalUtil();
            $paypalUtil->retrieveAuthorizedPayment($_GET['token']);

            if($paypalUtil->isCompleted()){

                $finalizedOrder = $paypalUtil->finalizeOrder();

                if(
                    !empty($finalizedOrder)
                    && $finalizedOrder->statusCode === PaypalStatusCodes::STATUS_CODE_CREATED
                    && $finalizedOrder->result->status === PaypalOrderStatus::ORDER_STATUS_COMPLETED
                ){
                    $orderEntity = $paypalUtil->getOrder($_GET['token']);

                    return view('welcome',[
                        'title' => 'Paid succesfully!'
                    ]);
                }


            }
        }


        return view('welcome',[
            'title' => 'There was an error with the payment!'
        ]);
    }

    public function cancelPayment()
    {
        return view('welcome',[
            'title' => 'Payment cancelled succesfully'
        ]);
    }
}
