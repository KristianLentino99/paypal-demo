<?php


namespace App\util\paypalv1;


use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
class Paypal
{

	const PAYMENT_STATUS = [
		'APPROVED' => 'approved'
	];
	const ENVIRONMENT_TEST = 'sandbox';
	const ENVIRONMENT_PRODUCTION = 'live';
	const CURRENT_ENVIRONMENT = 'sandbox';

	private ApiContext $apiContext;
	private PaypalPayment $paypalPayment;
	public function __construct()
	{
		$this->apiContext = self::newApiContext();
		$this->paypalPayment = new PaypalPayment($this->apiContext);
	}

	/**
	 * @return ApiContext
	 */
	private static function newApiContext() :ApiContext
	{
		$apiContext = new ApiContext(
			new OAuthTokenCredential(
				"{YOUR_CLIENT_ID}",
				"{YOUR_CLIENT_SECRET}"
			)
		);

		$apiContext->setConfig(
			array(
				'mode' => self::CURRENT_ENVIRONMENT
			)
		);
		return $apiContext;
	}

	/**
	 * @param string $paymentMethod
	 * @return Payer
	 */
	public function createPayer(string $paymentMethod = 'paypal'): Payer
	{
		$payer = new Payer();
		$payer->setPaymentMethod($paymentMethod);
		$this->paypalPayment->addPayer($payer);
		return $payer;
	}

	/**
	 * @param string $confirmUrl
	 * @param string $cancelUrl
	 * @return RedirectUrls
	 */
	public function createRedirectUrls(string $confirmUrl, string $cancelUrl): RedirectUrls
	{
		$redirectUrls = new RedirectUrls();
		$redirectUrls->setReturnUrl($confirmUrl)
			->setCancelUrl($cancelUrl);

		$this->paypalPayment->setRedirectUrls($redirectUrls);
		return $redirectUrls;
	}

	/**
	 * @param float $total
	 * @param string $currency
	 * @return Amount
	 */
	public function addAmount(float $total, string $currency = 'EUR'): Amount
	{
		// Set payment amount
		$amount = new Amount();
		$amount->setCurrency($currency)
			->setTotal($total);

		return $amount;
	}

	/**
	 * @param Amount $amount
	 * @param string $description
	 */
	public function createTransaction(Amount $amount, string $description)
	{
		$transaction = new Transaction();
		$transaction->setAmount($amount)
			->setDescription($description);

		$this->paypalPayment->addTransaction($transaction);
	}

	/**
	 * @param string $intent
	 * @return array
	 */
	public function createPayment(string $intent = 'sale'): array
	{
		$this->paypalPayment->createPaymentObject($intent);

		return $this->paypalPayment->createPayment();
	}

	/**
	 * @param string $paymentId
	 * @param string $PayerID
	 */
	public function retrievePaymentById(string $paymentId, string $PayerID)
	{
		$this->paypalPayment->findByPaymentId($paymentId);
		$this->paypalPayment->addExecution($PayerID);
	}

	/**
	 * @return Payment|void
	 */
	public function pay()
	{
		if($this->paypalPayment->getPayment() instanceof Payment){

			return $this->paypalPayment->confirmPayment();

		}
	}
}
