<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Epay extends CI_Controller {

	var $price;
	var $qty;
	var $subTotal;
	var $total;
	var $currency;
	var $lastName;
	var $firstName;
	var $cardholderName;
	var $cardNumber;
	var $ccExpMonth;
	var $ccExpYr;
	var $ccCvv;
	var $ccType;
	var $paymentError;
	var $payment_submission_url;
	var $braintree_submission_url;
	var $paypal_submission_url;
	var $merchant_account_id;
	var $tr_data;

	function __construct(){
		parent::__construct();
		$this->_braintree_init();

        $this->load->helper(array('form', 'url'));
	}

	public function index($currency = 'USD'){
		$this->currency = $currency;
		$this->_set_transaction_default();

		$data['payment_submission_url'] = $this->payment_submission_url;
		$data['tr_data'] = $this->tr_data;
		$data['selected_currency'] = $this->currency;

		$this->load->view('order_form', $data);
	}

	/**
	 * Helper method for getting an APIContext for all calls
	 * @param string $clientId Client ID
	 * @param string $clientSecret Client Secret
	 * @return PayPal\Rest\ApiContext
	 */
	private function _getApiContext(){
	    $apiContext = new \PayPal\Rest\ApiContext(
	        new \PayPal\Auth\OAuthTokenCredential(
	            'AYVF6hJjeSpJYW59Wux664s9fD7IQ4wtIYJ61l2740oYFx4v2NTHkTmsMEPStjCHqgoBT_Bt6ZEEY1js',
	            'EC2Q6W7WFeBjNXamsTpct2H54WqOQsDzAmzh4vBfDWv0PvcqZX6marfxBf-7q-XiHSzwC9LkktOdS6Ud'
	        )
	    );

	    // Comment this line out and uncomment the PP_CONFIG_PATH
	    // 'define' block if you want to use static file
	    // based configuration

	    $apiContext->setConfig(
	        array(
	            'mode' => 'sandbox',
	            'log.LogEnabled' => false,
	            'log.FileName' => '../PayPal.log',
	            'log.LogLevel' => 'DEBUG', // PLEASE USE `FINE` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
	            'cache.enabled' => false,
	            // 'http.CURLOPT_CONNECTTIMEOUT' => 30
	            // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
	        )
	    );

	    return $apiContext;
	}

	/**
	 * Validate post values before paypal transaction
	 * 
	 */
	public function process(){
		$this->load->library('form_validation');

		$this->form_validation->set_rules('transaction[amount]', 'Price', 'trim|numeric|required');
		$this->form_validation->set_rules('transaction[credit_card][number]', 'Credit Card Number', 'callback_luhn_check');//luhn_check
		$this->form_validation->set_rules('transaction[credit_card][cvv]', 'CCV Number', 'trim|required|min_length[3]|max_length[4]|is_natural');
		$this->form_validation->set_rules('transaction[credit_card][expiration_year]', 'Credit Card Expiration Year', 'trim|required|min_length[4]|max_length[4]|is_natural');

		if ($this->form_validation->run() == FALSE){
			$this->index();
		}else{
			$posted_values = $this->input->post('transaction', TRUE);
			//echo '<pre>';print_r($posted_values);echo '</pre>';exit();
			$this->price = $posted_values['amount'];
			$this->qty = 1;
			$this->subTotal = $this->price * $this->qty;
			$this->total = $this->subTotal;
			$this->currency = $this->input->post('cur', TRUE);
			$full_name = $this->input->post('full_name', TRUE);
			$full_name = explode(' ', $full_name);
			$this->lastName = array_pop($full_name);
			$this->firstName = implode(' ', $full_name);
			$this->cardholderName = $posted_values['credit_card']['cardholder_name'];
			$this->cardNumber = $posted_values['credit_card']['number'];
			$this->ccExpMonth = $posted_values['credit_card']['expiration_month'];
			$this->ccExpYr = $posted_values['credit_card']['expiration_year'];
			$this->ccCvv = $posted_values['credit_card']['cvv'];
			$this->ccType = $this->input->post('cc_type', TRUE);

			if($this->currency == 'USD' || $this->currency == 'EUR' || $this->currency == 'AUD'){
				if($this->ccType == 'amex' && $this->currency != 'USD'){
					$this->paymentError = '{"message": "AMEX is possible to use only for USD","information_link": ""}';
					$this->_handle_error();
				}else{
					$this->_pay_with_paypal();
				}
			}
		}
	}

	/**
	 * Process transaction using paypal REST API
	 * 
	 */
	private function _pay_with_paypal(){
		$apiContext = $this->_getApiContext();

		// ### CreditCard that can be used to fund a payment.
		$card = new PayPal\Api\CreditCard();
		$card->setType($this->ccType)
		    ->setNumber($this->cardNumber)
		    ->setExpireMonth($this->ccExpMonth)
		    ->setExpireYear($this->ccExpYr)
		    ->setCvv2($this->ccCvv)
		    ->setFirstName($this->firstName)
		    ->setLastName($this->lastName);

		// ### FundingInstrument
		$fi = new PayPal\Api\FundingInstrument();
		$fi->setCreditCard($card);

		// ### Payer
		$payer = new PayPal\Api\Payer();
		$payer->setPaymentMethod("credit_card")
		    ->setFundingInstruments(array($fi));

		// ### Itemized information
		$item1 = new PayPal\Api\Item();
		$item1->setName('Ground Coffee 40 oz')
		    ->setDescription('Ground Coffee 40 oz')
		    ->setCurrency($this->currency)
		    ->setQuantity($this->qty)
		    ->setPrice($this->price);

		$itemList = new PayPal\Api\ItemList();
		$itemList->setItems(array($item1));

		// ### Additional payment details such as tax, shipping charges etc.
		$details = new PayPal\Api\Details();
		$details->setSubtotal($this->subTotal);

		// ### Amount
		$amount = new PayPal\Api\Amount();
		$amount->setCurrency($this->currency)
		    ->setTotal($this->total)
		    ->setDetails($details);

		// ### Transaction
		$transaction = new PayPal\Api\Transaction();
		$transaction->setAmount($amount)
		    ->setItemList($itemList)
		    ->setDescription("Payment description demo")
		    ->setInvoiceNumber(uniqid());

		// ### Payment
		$payment = new PayPal\Api\Payment();
		$payment->setIntent("sale")
		    ->setPayer($payer)
		    ->setTransactions(array($transaction));

		// For Sample Purposes Only.
		//$request = clone $payment;

		// ### Create Payment
		try {
		    $payment->create($apiContext);
		    $this->_paypal_process_result($payment);
		} catch (\PayPal\Exception\PayPalConnectionException $ex) {
			$this->paymentError = $ex->getData();
			$this->_handle_error();
		}

	} //end _pay_with_paypal()

	private function _paypal_process_result($result){
		$this->load->model('Transaction_model');
		$this->load->model('Item_model');
		$this->load->library('session');

		$result = json_decode($result); //echo '<pre>';print_r($result);echo '</pre>';
		$parsed_data = array();
		$parsed_data['payment_id'] = $result->id;
		
		foreach ($result->transactions as $transaction) {
			$parsed_data['total'] = $transaction->amount->total;
			$parsed_data['currency'] = $transaction->amount->currency;
			$parsed_data['subtotal'] = $transaction->amount->details->subtotal;
			$parsed_data['description'] = $transaction->description;
			$parsed_data['invoice_number'] = $transaction->invoice_number;

			foreach ($transaction->item_list->items as $item) {
				$item_data['invoice_number'] = $parsed_data['invoice_number'];
				$item_data['item_name'] = $item->name;
				$item_data['price'] = $item->price;
				$item_data['currency'] = $item->currency;
				$item_data['quantity'] = $item->quantity;
				$item_data['description'] = $item->description;

				//insert into 'items' table
				$this->Item_model->add_item($item_data);
			}
			//insert into 'transactions' table
			$this->Transaction_model->add_transaction($parsed_data);

			$parsed_data['items'][] = $item_data;
		}

		$parsed_data['msg'] = 'Transaction Successfull.';
		$parsed_data['msg_type'] = 'success';

		//redirect in order to prevent re-submission
		$this->session->set_userdata('parsed_data', $parsed_data);
		redirect('/epay/result/', 'refresh');
	}

	/**
	 * General function to process error data
	 * 
	 */
	private function _handle_error(){
		$this->load->library('session');
		$err_data = json_decode($this->paymentError);
		$this->session->set_userdata('err_data', $err_data);
		//$this->load->view('error_result', $err_data);
		redirect('/epay/result/error', 'refresh');
	}

	/**
	 * General function to display success/error messages
	 * 
	 */
	public function result($err = ''){
		$this->load->library('session');

		if($err == 'error'){
			$err_data = $this->session->userdata('err_data');
			$this->session->unset_userdata('err_data');
			$this->load->view('error_result', $err_data);
		}else{
			$parsed_data = $this->session->userdata('parsed_data');
			$this->session->unset_userdata('parsed_data');
			$this->load->view('payment_result', $parsed_data);
		}
	}

	/**
	 * Initializes Braintree payment gateway
	 * 
	 */
	private function _braintree_init(){
		Braintree_Configuration::environment('sandbox');
		Braintree_Configuration::merchantId('j8zgt44rd2m6dt74');
		Braintree_Configuration::publicKey('w6myqf4pd67bhn8v');
		Braintree_Configuration::privateKey('d2a87ea92ccf38881109be930c07e7da');

		$this->braintree_submission_url = Braintree_TransparentRedirect::url();
		//return $clientToken = Braintree_ClientToken::generate();
	}

	/**
	 * Process result returned after braintree transactioin
	 * 
	 */
	private function _braintree_process_result($transaction, $transaction_id){
		$this->load->model('Transaction_model');
		$this->load->model('Item_model');
		
		$parsed_data['payment_id'] = $transaction_id;
		$parsed_data['total'] = $transaction->amount;
		$parsed_data['currency'] = $transaction->currencyIsoCode;
		$parsed_data['invoice_number'] = $transaction->id;

		$item_data['invoice_number'] = $transaction->id;
		$item_data['item_name'] = 'Sample Product';
		$item_data['price'] = $transaction->amount;
		$item_data['currency'] = $transaction->currencyIsoCode;
		$item_data['quantity'] = 1;

		//insert into 'items' table
		$this->Item_model->add_item($item_data);
		
		//insert into 'transactions' table
		$this->Transaction_model->add_transaction($parsed_data);

		$parsed_data['items'][] = $item_data;
		$parsed_data['msg'] = 'Transaction Successfull.';
		$parsed_data['msg_type'] = 'success';

		$this->load->view('payment_result', $parsed_data);
	}

	/**
	 * Performs transaction using Braintree TransparentRedirect
	 * 
	 */
	public function braintree(){
		if (isset($_GET["id"])) {
			$transaction_id = $_GET["id"];
			//$this->_braintree_init();
			try{
				$result = Braintree_TransparentRedirect::confirm($_SERVER['QUERY_STRING']);

				if (isset($result) && $result->success) {
					$transaction = $result->transaction;
					//echo '<pre>';print_r($result);echo '</pre>';
					$this->_braintree_process_result($transaction, $transaction_id);
				}else{
				    foreach($result->errors->deepAll() as $error) {
				        echo('<div style="color: red;">' . $error->message . '</div>');
				    }
				    $parsed_data['msg'] = 'Transaction Failed.';
					$parsed_data['msg_type'] = 'error';
				}
			} catch (Braintree_Exception_NotFound $ex){
				//echo $ex->getMessage();
				//payment not success
				$this->paymentError = '{"message": "Wrong data passed.","information_link": ""}';
				$this->_handle_error();
			}
		}else{
			//payment not success
			$this->paymentError = '{"message": "Payment was not successfull.","information_link": ""}';
			$this->_handle_error();
		}
	}

	/**
	 * Used while doing transaction using Braintree in order to select 
	 * merchant_account_id for different currencies
	 */
	private function _set_transaction_default(){
		switch ($this->currency) {
			case 'THB':
				$this->merchant_account_id = 'accept_thb';
				$this->payment_submission_url = $this->braintree_submission_url;
				break;
			case 'HKD':
				$this->merchant_account_id = 'accept_hkd';
				$this->payment_submission_url = $this->braintree_submission_url;
				break;
			case 'SGD':
				$this->merchant_account_id = 'accept_sgd';
				$this->payment_submission_url = $this->braintree_submission_url;
				break;
			default:
				$this->merchant_account_id = 'wwwmuhibulcom';
				$this->payment_submission_url = site_url('epay/process');
				break;
		}

		if($this->merchant_account_id == 'wwwmuhibulcom'){
			$this->tr_data = '';
		}else{
			$this->tr_data = Braintree_TransparentRedirect::transactionData(
						array(
							'redirectUrl' => site_url("epay/braintree"),
							'transaction' => array(
								'type' => 'sale',
								'merchantAccountId' => $this->merchant_account_id
							)
						)
					);
		}
	}

	/* Luhn algorithm number checker - (c) 2005-2008 shaman - www.planzero.org */
	public function luhn_check($num) {
		if(empty(trim($num))){
			$msg = 'The %s is required.';
			$result = FALSE;
		}else if(!is_numeric($num)){
			$msg = 'The %s should contain numeric values only.';
			$result = FALSE;
		}else{
			$num = preg_replace('/[^\d]/', '', $num);
		    settype($num, 'string');
			$sumTable = array(
				array(0,1,2,3,4,5,6,7,8,9),
				array(0,2,4,6,8,1,3,5,7,9)
			);
			$sum = 0;
			$flip = 0;
			// If the total mod 10 equals 0, the number is valid
			for ($i = strlen($num) - 1; $i >= 0; $i--) {
				$sum += $sumTable[$flip++ & 0x1][$num[$i]];
			}
			$result = $sum % 10 === 0;
		    $msg = 'The %s is not valid. Please use numeric values only with no space or any special characters';
		}
		
		if(!$result){
			$this->form_validation->set_message('luhn_check', $msg);
			return FALSE;
		}else{
			return TRUE;
		}
		

	}
}
