<?php

class SubscriptionResponse {
	
	public $result_code;
	public $message_code;
	public $message_text;
	public $subscription_id;
	public $invoice_number;
	public $description;
	public $amount;
	public $address;
	public $city;
	public $state;
	public $zip_code;
	public $country;
	public $phone;
	public $email;
	
	function __construct($response, $invoice_number, $description, $amount, $credit_card, $phone, $email) {
		$this->result_code = $this->substring_between($response,'<resultCode>','</resultCode>');
		$this->message_code = $this->substring_between($response,'<code>','</code>');
		$this->message_text = $this->substring_between($response,'<text>','</text>');
		$this->subscription_id = $this->substring_between($response,'<subscriptionId>','</subscriptionId>');
		$this->invoice_number = $invoice_number;
		$this->description = $description;
		$this->amount = $amount;
		$this->address = $credit_card->street_address;
		$this->city = $credit_card->city;
		$this->state = $credit_card->state;
		$this->zip_code = $credit_card->zip_code;
		$this->country = $credit_card->country;
		$this->phone = $phone;
		$this->email = $email;
	}
	
	//helper function for parsing response
	function substring_between($haystack,$start,$end) 
	{
		if (strpos($haystack,$start) === false || strpos($haystack,$end) === false) 
		{
			return false;
		} 
		else 
		{
			$start_position = strpos($haystack,$start)+strlen($start);
			$end_position = strpos($haystack,$end);
			return substr($haystack,$start_position,$end_position-$start_position);
		}
	}
	
}
?>