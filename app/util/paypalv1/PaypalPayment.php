<?php


namespace App\util\paypalv1;


use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Rest\ApiContext;
use yii\db\Exception;

class PaypalPayment
{
	protected ApiContext $apiContext;
	private Payer $payer;
	private RedirectUrls $redirectUrls;
	/**
	 * @var $transactions Transaction[]
	 */
	private array $transactions;
	private Payment $payment;
	/**
	 * @var PaymentExecution
	 */
	private PaymentExecution $paymentExecution;

	public function __construct($apiContext)
	{
		$this->apiContext = $apiContext;
	}

	public function addPayer(Payer $payer)
	{
		$this->payer = $payer;
	}

	/**
	 * @return Payer
	 */
	public function getPayer(): Payer
	{
		return $this->payer;
	}

	/**
	 * @return RedirectUrls
	 */
	public function getRedirectUrls(): RedirectUrls
	{
		return $this->redirectUrls;
	}

	/**
	 * @param RedirectUrls $redirectUrls
	 */
	public function setRedirectUrls(RedirectUrls $redirectUrls): void
	{
		$this->redirectUrls = $redirectUrls;
	}

	/**
	 * @return Transaction[]
	 */
	public function getTransactions(): array
	{
		return $this->transactions;
	}

	/**
	 * @param Transaction[] $transactions
	 */
	public function setTransactions(array $transactions): void
	{
		$this->transactions = $transactions;
	}
	/**
	 * @param Transaction $transaction
	 */
	public function addTransaction(Transaction $transaction): void
	{
		$this->transactions[] = $transaction;
	}

	/**
	 * @return Payment
	 */
	public function getPayment(): Payment
	{
		return $this->payment;
	}

	/**
	 * @param Payment $payment
	 */
	public function setPayment(Payment $payment): void
	{
		$this->payment = $payment;
	}

	/**
	 * @return PaymentExecution
	 */
	public function getPaymentExecution(): PaymentExecution
	{
		return $this->paymentExecution;
	}

	/**
	 * @param PaymentExecution $paymentExecution
	 */
	public function setPaymentExecution(PaymentExecution $paymentExecution): void
	{
		$this->paymentExecution = $paymentExecution;
	}
	/**
	 * @param string $intent
	 */
	public function createPaymentObject(string $intent)
	{
		// Create the full payment object
		$payment = new Payment();
		$payment->setIntent($intent)
			->setPayer($this->getPayer())
			->setRedirectUrls($this->getRedirectUrls())
			->setTransactions($this->getTransactions());

		$this->payment = $payment;
	}

	/**
	 * @return array
	 */
	public function createPayment(): array
	{
		$success = true;
		$approvalUrl = null;
		try {

			$this->payment->create($this->apiContext);
			// Get PayPal redirect URL and redirect the customer
			$approvalUrl = $this->payment->getApprovalLink();

			// Redirect the customer to $approvalUrl
		} catch (PayPal\Exception\PayPalConnectionException $ex) {
			$success = false;
		}

		return [
			'success' => $success,
			'approvalUrl' => $approvalUrl
		];

	}

	/**
	 * @param string $paymentId
	 * @return void
	 */
	public function findByPaymentId(string $paymentId)
	{
		$this->payment = Payment::get($paymentId,$this->apiContext);
	}

	public function addExecution(string $PayerID)
	{
		$this->paymentExecution = new PaymentExecution();
		$this->paymentExecution->setPayerId($PayerID);
	}

	/**
	 * @return Payment
	 */
	public function confirmPayment(): Payment
	{
		//esegue definitivamente il pagamento
		return $this->payment->execute($this->paymentExecution, $this->apiContext);
	}


}
