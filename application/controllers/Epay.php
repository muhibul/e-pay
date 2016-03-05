<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Epay extends CI_Controller {

	var $price;
	var $qty;
	var $sub_total;
	var $total;
	var $currency;
	var $lastName;
	var $firstName;
	var $cardholderName;
	var $cardNumber;
	var $cc_exp_month;
	var $cc_exp_yr;
	var $cc_ccv;
	var $cc_type;
	var $payment_error;

	function __construct(){
		parent::__construct();
		
        $this->load->helper(array('form', 'url'));
	}

	public function index(){
		$this->_braintree_init();
		$data['url'] = Braintree_TransparentRedirect::url();
		$data['tr_data'] = Braintree_TransparentRedirect::transactionData(
						array(
							'redirectUrl' => site_url("epay/braintree"),
							'transaction' => array(
								'type' => 'sale',
							)
						)
					);

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
	            'log.LogEnabled' => true,
	            'log.FileName' => '../PayPal.log',
	            'log.LogLevel' => 'DEBUG', // PLEASE USE `FINE` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
	            'cache.enabled' => true,
	            // 'http.CURLOPT_CONNECTTIMEOUT' => 30
	            // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
	        )
	    );

	    // Partner Attribution Id
	    // Use this header if you are a PayPal partner. Specify a unique BN Code to receive revenue attribution.
	    // To learn more or to request a BN Code, contact your Partner Manager or visit the PayPal Partner Portal
	    // $apiContext->addRequestHeader('PayPal-Partner-Attribution-Id', '123123123');

	    return $apiContext;
	}

	public function process(){
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');

		$this->form_validation->set_rules('transaction[amount]', 'Price', 'trim|required');
		$this->form_validation->set_rules('transaction[credit_card][cvv]', 'CCV Number', 'trim|required|min_length[3]|max_length[4]|is_natural');
		$this->form_validation->set_rules('transaction[credit_card][expiration_year]', 'Credit Card Expiration Year', 'trim|required|min_length[4]|max_length[4]|is_natural');

		if ($this->form_validation->run() == FALSE){
			$this->index();
		}else{
			$posted_values = $this->input->post('transaction', TRUE);
			//echo '<pre>';print_r($posted_values);echo '</pre>';exit();
			$this->price = $posted_values['amount'];
			$this->qty = 1;
			$this->sub_total = $this->price * $this->qty;
			$this->total = $this->sub_total;
			$this->currency = $this->input->post('cur', TRUE);
			$full_name = $this->input->post('full_name', TRUE);
			$full_name = explode(' ', $full_name);
			$this->lastName = array_pop($full_name);
			$this->firstName = implode(' ', $full_name);
			$this->cardholderName = $posted_values['credit_card']['cardholder_name'];
			$this->cardNumber = $posted_values['credit_card']['number'];
			$this->cc_exp_month = $posted_values['credit_card']['expiration_month'];
			$this->cc_exp_yr = $posted_values['credit_card']['expiration_year'];
			$this->cc_ccv = $posted_values['credit_card']['cvv'];
			$this->cc_type = $this->input->post('cc_type', TRUE);

			if($this->currency == 'USD' || $this->currency == 'EUR' || $this->currency == 'AUD'){
				if($this->cc_type == 'amex' && $this->currency != 'USD'){
					$this->payment_error = '{"message": "AMEX is possible to use only for USD","information_link": ""}';
					$this->_handle_error();
				}else{
					$this->_pay_with_paypal();
				}
			}else{
				$payment = $this->_braintree_transparent_redirect();
				//$payment = $this->_pay_with_braintree();
			}
		}
	}

	private function _paypal_process_result($result){
		$this->load->model('Transaction_model');
		$this->load->model('Item_model');

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

		$this->load->view('payment_result', $parsed_data);
	}

	private function _pay_with_paypal(){
		$apiContext = $this->_getApiContext();

		// ### CreditCard
		// A resource representing a credit card that can be
		// used to fund a payment.
		$card = new PayPal\Api\CreditCard();
		$card->setType($this->cc_type)
		    ->setNumber($this->cardNumber)
		    ->setExpireMonth($this->cc_exp_month)
		    ->setExpireYear($this->cc_exp_yr)
		    ->setCvv2($this->cc_ccv)
		    ->setFirstName($this->firstName)
		    ->setLastName($this->lastName);

		// ### FundingInstrument
		// A resource representing a Payer's funding instrument.
		// For direct credit card payments, set the CreditCard
		// field on this object.
		$fi = new PayPal\Api\FundingInstrument();
		$fi->setCreditCard($card);

		// ### Payer
		// A resource representing a Payer that funds a payment
		// For direct credit card payments, set payment method
		// to 'credit_card' and add an array of funding instruments.
		$payer = new PayPal\Api\Payer();
		$payer->setPaymentMethod("credit_card")
		    ->setFundingInstruments(array($fi));

		// ### Itemized information
		// (Optional) Lets you specify item wise
		// information
		$item1 = new PayPal\Api\Item();
		$item1->setName('Ground Coffee 40 oz')
		    ->setDescription('Ground Coffee 40 oz')
		    ->setCurrency($this->currency)
		    ->setQuantity($this->qty)
		    ->setPrice($this->price);

		$itemList = new PayPal\Api\ItemList();
		$itemList->setItems(array($item1));

		// ### Additional payment details
		// Use this optional field to set additional
		// payment information such as tax, shipping
		// charges etc.
		$details = new PayPal\Api\Details();
		$details->setSubtotal($this->sub_total);

		// ### Amount
		// Lets you specify a payment amount.
		// You can also specify additional details
		// such as shipping, tax.
		$amount = new PayPal\Api\Amount();
		$amount->setCurrency($this->currency)
		    ->setTotal($this->total)
		    ->setDetails($details);

		// ### Transaction
		// A transaction defines the contract of a
		// payment - what is the payment for and who
		// is fulfilling it. 
		$transaction = new PayPal\Api\Transaction();
		$transaction->setAmount($amount)
		    ->setItemList($itemList)
		    ->setDescription("Payment description")
		    ->setInvoiceNumber(uniqid());

		// ### Payment
		// A Payment Resource; create one using
		// the above types and intent set to sale 'sale'
		$payment = new PayPal\Api\Payment();
		$payment->setIntent("sale")
		    ->setPayer($payer)
		    ->setTransactions(array($transaction));

		// For Sample Purposes Only.
		$request = clone $payment;

		// ### Create Payment
		try {
		    $payment->create($apiContext);
		    $this->_paypal_process_result($payment);
		} catch (\PayPal\Exception\PayPalConnectionException $ex) {
			$this->payment_error = $ex->getData();
			$this->_handle_error();
		}

	} //end _pay_with_paypal()

	private function _handle_error(){
		$err_data = json_decode($this->payment_error);
		$this->load->view('error_result', $err_data);
	}

	private function _braintree_init(){
		Braintree_Configuration::environment('sandbox');
		Braintree_Configuration::merchantId('j8zgt44rd2m6dt74');
		Braintree_Configuration::publicKey('w6myqf4pd67bhn8v');
		Braintree_Configuration::privateKey('d2a87ea92ccf38881109be930c07e7da');

		//$braintreeUrl = Braintree_TransparentRedirect::url();
		//return $clientToken = Braintree_ClientToken::generate();
	}

	private function _braintree_process_result($transaction, $transaction_id){
		$this->load->model('Transaction_model');
		$this->load->model('Item_model');
		
		$parsed_data['payment_id'] = $transaction_id;
		$parsed_data['total'] = $transaction->amount;
		$parsed_data['currency'] = $transaction->currencyIsoCode;
		// $parsed_data['subtotal'] = $transaction->amount->details->subtotal;
		// $parsed_data['description'] = $transaction->description;
		$parsed_data['invoice_number'] = $transaction->id;

		$item_data['invoice_number'] = $transaction->id;
		$item_data['item_name'] = 'Sample Product';
		$item_data['price'] = $transaction->amount;
		$item_data['currency'] = $transaction->currencyIsoCode;
		$item_data['quantity'] = 1;
		// $item_data['description'] = $item->description;

		//insert into 'items' table
		$this->Item_model->add_item($item_data);
		
		//insert into 'transactions' table
		$this->Transaction_model->add_transaction($parsed_data);

		$parsed_data['items'][] = $item_data;
		$parsed_data['msg'] = 'Transaction Successfull.';
		$parsed_data['msg_type'] = 'success';

		$this->load->view('payment_result', $parsed_data);
	}

	public function braintree(){
		if (isset($_GET["id"])) {
			$transaction_id = $_GET["id"];
			$this->_braintree_init();
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
				//echo '<pre>';print_r($result->errors);echo '</pre>';
			}
		}else{
			//payment not success
			$this->payment_error = '{"message": "Payment was not successfull.","information_link": ""}';
			$this->_handle_error();
		}
	}

	private function _braintree_transparent_redirect(){
		$this->_braintree_init();
		$url = Braintree_TransparentRedirect::url();
		//echo '<pre>';print_r($url);echo '</pre>';

		switch ($this->currency) {
			case 'THB':
				$merchantAccountId = 'accept_thb';
				break;
			case 'HKD':
				$merchantAccountId = 'accept_hkd';
				break;
			case 'SGD':
				$merchantAccountId = 'accept_sgd';
				break;
			default:
				$merchantAccountId = 'wwwmuhibulcom';
				break;
		}


		$tr_data = Braintree_TransparentRedirect::transactionData(
						array(
							'redirectUrl' => "http://" . $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH),
							'transaction' => array(
								'amount' => $this->total, 
								'type' => 'sale'
							)
						)
					);
		echo '<pre>';print_r($tr_data);echo '</pre>';

		// set post fields //transaction[customer][first_name]
		$transaction = array(
			'transaction' => array(
				'amount' => $this->total,
				'merchant_account_id' => $merchantAccountId,
				'credit_card' => array(
					'cardholder_name' => $this->cardholderName,
					'number' => $this->cardNumber,
					'expiration_date' => $this->cc_exp_month.'/'.$this->cc_exp_yr,
					'cvv' => $this->cc_ccv,
				),
				'customer' => array(
					'first_name' => $this->firstName,
					'last_name' => $this->lastName,
					'company' => 'Braintree',
					'email' => 'dan@example.com',
					'phone' => '419-555-1234',
					'fax' => '419-555-1235',
					'website' => 'http://braintreepayments.com'
				),
			),
			'tr_data' => $tr_data
		);
		$postvars = http_build_query($transaction);
		// echo '<pre>';print_r($postvars);echo '</pre>';

		/*$transaction = [
			'transaction' => [
				'amount' => $this->total,
				'merchant_account_id' => $merchantAccountId,
				'credit_card' => [
					'cardholder_name' => $this->cardholderName,
					'number' => $this->cardNumber,//4148529247832259, 5105105105105100
					'expiration_date' => $this->cc_exp_month.'/'.$this->cc_exp_yr,
					'cvv' => $this->cc_ccv,
				],
				'customer' => [
					'first_name' => $this->firstName,
					'last_name' => $this->lastName,
					'company' => 'Braintree',
					'email' => 'dan@example.com',
					'phone' => '419-555-1234',
					'fax' => '419-555-1235',
					'website' => 'http://braintreepayments.com'
				]
			],
			'tr_data' => $tr_data
		];*/
		/*$transaction['transaction']['amount'] = $this->total;
		$transaction['transaction']['merchant_account_id'] = $merchantAccountId;
		$transaction['transaction']['credit_card']['cardholder_name'] = $this->cardholderName;
		$transaction['transaction']['credit_card']['number'] = $this->cardholderName;
		$transaction['transaction']['credit_card']['expiration_date'] = $this->cc_exp_month.'/'.$this->cc_exp_yr;
		$transaction['transaction']['credit_card']['cvv'] = $this->cc_ccv;
		$transaction['transaction']['customer']['first_name'] = $this->firstName;
		$transaction['transaction']['customer']['last_name'] = $this->lastName;
		$transaction['tr_data'] = $tr_data;*/
		// echo '<pre>';print_r($transaction);echo '</pre>';
		//exit();

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
		// curl_setopt($ch, CURLOPT_HTTPHEADER, http_build_query($transaction['transaction']));
		$response = curl_exec($ch);
		echo '<pre>';print_r($response);echo '</pre>';
		curl_close($ch);
		
		if (isset($_GET["id"])) {
            $result = Braintree_TransparentRedirect::confirm($_SERVER['QUERY_STRING']);
        }
        redirect('/login/form/', 'refresh');
		//var_dump($response);
	}

	private function _pay_with_braintree(){
		$this->_braintree_init();
		switch ($this->currency) {
			case 'THB':
				$merchantAccountId = 'accept_thb';
				break;
			case 'HKD':
				$merchantAccountId = 'accept_hkd';
				break;
			case 'SGD':
				$merchantAccountId = 'accept_sgd';
				break;
			default:
				$merchantAccountId = 'wwwmuhibulcom';
				break;
		}
		echo $number = $this->cardNumber;

		try {
		    $transaction = Braintree_Transaction::saleNoValidate(array(
			    'amount' => $this->total,
			    'merchantAccountId' => $merchantAccountId,
			    'creditCard' => array(
			        'cardholderName' => $this->cardholderName,
			        'number' => $this->cardNumber,//4148529247832259, 5105105105105100
			        'expirationDate' => $this->cc_exp_month.'/'.$this->cc_exp_yr,
			        'cvv' => $this->cc_ccv,
			    ),
			    'customer' => array(
			        'firstName' => $this->firstName,
			        'lastName' => $this->lastName,
			        'company' => 'Braintree',
			        'email' => 'dan@example.com',
			        'phone' => '419-555-1234',
			        'fax' => '419-555-1235',
			        'website' => 'http://braintreepayments.com'
			    ),
			));
			echo '<pre>';print_r($transaction);echo '</pre>';
		} catch (Braintree_Exception_ForgedQueryString $ex) {
			echo $ex->getMessage();
		} catch (Braintree_Exception_Authentication $ex){
			echo $ex->getMessage();
		} catch (Braintree_Exception_Authorization $ex){
			echo $ex->getMessage();
		} catch (Braintree_Exception_Configuration $ex){
			echo $ex->getMessage();
		} catch (Braintree_Exception_DownForMaintenance $ex){
			echo $ex->getMessage();
		} catch (Braintree_Exception_InvalidChallenge $ex){
			echo $ex->getMessage();
		} catch (Braintree_Exception_InvalidSignature $ex){
			echo $ex->getMessage();
		} catch (Braintree_Exception_NotFound $ex){
			echo $ex->getMessage();
		} catch (Braintree_Exception_ServerError $ex){
			echo $ex->getMessage();
		} catch (Braintree_Exception_SSLCertificate $ex){
			echo $ex->getMessage();
		} catch (Braintree_Exception_Unexpected $ex){
			echo $ex->getMessage();
		} catch (Braintree_Exception_UpgradeRequired $ex){
			echo $ex->getMessage();
		} catch (Braintree_Exception_ValidationsFailed $ex){
			echo $ex->getMessage();
		}
		

		/*$transaction = Braintree_Transaction::saleNoValidate(array(
			'amount' => $this->total,
			'merchantAccountId' => $marchant_account_id,
			'creditCard' => array(
				'cardholderName' => $this->cardholderName,
				'number' => $this->cardNumber,
				'expirationDate' => $this->cc_exp_month.'/'.$this->cc_exp_yr,
				'cvv' => $this->cc_ccv,
			),
			'customer' => array(
				'firstName' => $this->firstName,
				'lastName' => $this->lastName,
				'company' => 'Braintree',
				'email' => 'dan@example.com',
				'phone' => '419-555-1234',
				'fax' => '419-555-1235',
				'website' => 'http://braintreepayments.com'
			),
		));*/
		//echo '<pre>';print_r($transaction);echo '</pre>';
	}
}
