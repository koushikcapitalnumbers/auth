<?php

class Payment {
	public $invoice_number;
	public $mode; // live, test-live, test-url
	public $fund; // general fund, children's fund
	
	private $auth_net_clientid;
	private $auth_net_key;
	private $post_url;
	private $recurring_host;
	private $recurring_path;
		
	function __construct($invoice_number, $mode, $fund) {
		
		$this->invoice_number = $invoice_number;
		$this->mode = $mode;
		$this->fund = $fund;
		
		global $merchant_config;
	
		switch ($this->mode) {
			case "live":			
			case "test-live":
				$this->auth_net_clientid = $merchant_config[$fund]['clientid'];
				$this->auth_net_key = $merchant_config[$fund]['key'];		
				$this->post_url = "https://secure.authorize.net/gateway/transact.dll"; // Used with One Time Payments
				$this->recurring_host = "api.authorize.net"; // Used with Recurring Payments
				$this->recurring_path = "/xml/v1/request.api"; // Used with Recurring Payments
				break;
			case "test-url":
				$this->auth_net_clientid = $merchant_config['test']['clientid'];
				$this->auth_net_key = $merchant_config['test']['key'];
				$this->post_url = "https://test.authorize.net/gateway/transact.dll"; // Used with One Time Payments
				$this->recurring_host = "apitest.authorize.net"; // Used with Recurring Payments
				$this->recurring_path = "/xml/v1/request.api"; // Used with Recurring Payments
				break;
			default:
				throw new Exception("Exception while creating Payment Object: '{$this->mode}' is not a valid payment mode.");
		}
	}
	
	function ProcessSinglePayment($description, $email, $phone, $amount, $credit_card) {
		
		if ($this->mode == 'live') {
			$test_request = false;
		} else {
			$test_request = true;
		}
		
		// build $post_values array for Authorize.NET
		$post_values = array(
			"x_test_request" 	=> "$test_request",
			"x_login"			=> "{$this->auth_net_clientid}",
			"x_tran_key"		=> "{$this->auth_net_key}",

			"x_version"			=> "3.1",
			"x_delim_data"		=> "TRUE",
			"x_delim_char"		=> "|",
			"x_relay_response"	=> "FALSE",

			"x_type"			=> "AUTH_CAPTURE",
			"x_method"			=> "CC",

			"x_description"		=> "$description",
			"x_invoice_num"		=> "{$this->invoice_number}",
			"x_email"			=> "$email",
			"x_phone" 			=> "$phone",
			
			"x_amount"			=> "$amount",
			"x_card_num" 		=> "{$credit_card->number}",
			"x_exp_date"		=> "{$credit_card->expiration_month}" . "{$credit_card->expiration_year}",
			"x_card_code"		=> "{$credit_card->security_code}",
			
			"x_first_name"		=> "{$credit_card->first_name}",
			"x_last_name"		=> "{$credit_card->last_name}",
			"x_address"			=> "{$credit_card->street_address}",
			"x_city"			=> "{$credit_card->city}",
			"x_state"			=> "{$credit_card->state}",
			"x_zip"				=> "{$credit_card->zip_code}",
			"x_country"			=> "{$credit_card->country}"
			// Additional fields can be added here as outlined in the AIM integration
			// guide at: http://developer.authorize.net
		);
		
		// This section takes the input fields and converts them to the proper format
		// for an http post.  For example: "x_login=username&x_tran_key=a1B2c3D4"
		$post_string = "";
		foreach( $post_values as $key => $value )
			{ $post_string .= "$key=" . urlencode( $value ) . "&"; }
		$post_string = rtrim( $post_string, "& " );

		// This code uses the CURL library for php to establish a connection,
		// submit the post, and record the response.
		// If you receive an error, you may want to ensure that you have the curl
		// library enabled in your php configuration
		$request = curl_init($this->post_url); // initiate curl object
			curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
			curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
			curl_setopt($request, CURLOPT_POSTFIELDS, $post_string); // use HTTP POST to send form data
			curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response.
			$post_response = curl_exec($request); // execute curl post and store results in $post_response
			// additional options may be required depending upon your server configuration
			// you can find documentation on curl options at http://www.php.net/curl_setopt
		curl_close ($request); // close curl object

		// This line takes the response and breaks it into an array using the specified delimiting character
		$transaction_response_array = explode($post_values["x_delim_char"],$post_response);
		
		$transaction_response = new TransactionResponse($transaction_response_array);
		
		return $transaction_response;
	}
	
	
	function ProcessRecurringPayment($description, $email, $phone, $amount, $frequency, $credit_card) {
		
		//build xml to post
		$content =
		        "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
		        "<ARBCreateSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
		        "<merchantAuthentication>".
		        "<name>" . $this->auth_net_clientid . "</name>".
		        "<transactionKey>" . $this->auth_net_key . "</transactionKey>".
		        "</merchantAuthentication>".
		        "<subscription>".
		        "<name>UMC Foundation Gift</name>".
		        "<paymentSchedule>".
		        "<interval>".
		        "<length>1</length>".
		        "<unit>months</unit>".
		        "</interval>".
		        "<startDate>" . date('Y-m-d') . "</startDate>".
		        "<totalOccurrences>". $frequency . "</totalOccurrences>".
		        "</paymentSchedule>".
		        "<amount>". $amount ."</amount>".
		        "<payment>".
		        "<creditCard>".
		        "<cardNumber>" . $credit_card->number . "</cardNumber>".
		        "<expirationDate>" . $credit_card->expiration_year . "-" . $credit_card->expiration_month . "</expirationDate>".
				"<cardCode>" . $credit_card->security_code . "</cardCode>" . 
		        "</creditCard>".
		        "</payment>".
				"<order>" . 
				"<invoiceNumber>" . $this->invoice_number . "</invoiceNumber>" . 
				"<description>" . $description . "</description>" . 
				"</order>" . 
				"<customer>" . 
				"<email>" . $email . "</email>" . 
				"<phoneNumber>" . $phone . "</phoneNumber>" . 
				"</customer>" .
		        "<billTo>".
		        "<firstName>". $credit_card->first_name . "</firstName>".
		        "<lastName>" . $credit_card->last_name . "</lastName>".
				"<address>" . $credit_card->street_address . "</address>" . 
				"<city>" . $credit_card->city . "</city>" . 
				"<state>" . $credit_card->state . "</state>" . 
				"<zip>" . $credit_card->zip_code . "</zip>" . 
				"<country>" . $credit_card->country . "</country>" .
		        "</billTo>".
		        "</subscription>".
		        "</ARBCreateSubscriptionRequest>";
		
			$posturl = "https://" . $this->recurring_host . $this->recurring_path;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $posturl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$subscription_response_xml = curl_exec($ch);
			
			if ($subscription_response_xml) {
				$subscription_response = new SubscriptionResponse($subscription_response_xml, $this->invoice_number, $description, $amount, $credit_card, $phone, $email);
				return $subscription_response;
				
			} else {
				return false;
			}
			
		
	}
}




?>