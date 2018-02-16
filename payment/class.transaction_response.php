<?php

class TransactionResponse {
	
	public $invoice_number;
	public $response_code;
	public $response_subcode;
	public $response_reason_code;
	public $response_reason_text;
	public $authorization_code;
	public $avs_response;
	public $transaction_id;
	public $description;
	public $amount;
	public $address;
	public $city;
	public $state;
	public $zip_code;
	public $country;
	public $phone;
	public $email;
	public $md5_hash;
	public $card_code_response;
	
	function __construct($response) {
		$this->invoice_number = $response[7];
		$this->response_code = $response[0];
		$this->response_subcode = $response[1];
		$this->response_reason_code = $response[2];
		$this->response_reason_text = $response[3];
		$this->authorization_code = $response[4];
		$this->avs_response = $response[5];
		$this->transaction_id = $response[6];
		$this->description = $response[8];
		$this->amount = $response[9];
		$this->address = $response[16];
		$this->city = $response[17];
		$this->state = $response[18];
		$this->zip_code = $response[19];
		$this->country = $response[20];
		$this->phone = $response[21];
		$this->email = $response[23];
		$this->md5_hash = $response[37];
		$this->card_code_response = $response[38];
	}
	
}


?>