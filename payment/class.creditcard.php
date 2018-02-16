<?php

class CreditCard {
	public $first_name;
	public $last_name;
	public $number;
	public $expiration_month;
	public $expiration_year;
	public $security_code;
	public $street_address;
	public $address2;
	public $city;
	public $state;
	public $zip_code;
	public $country;
	
	function __construct($first_name, $last_name, $number, $expiration_month, $expiration_year, $security_code, $street_address, $address2, $city, $state,  $zip_code, $country) {
		$this->first_name = $first_name;
		$this->last_name = $last_name;
		$this->number = $number;
		$this->expiration_month = $expiration_month;
		$this->expiration_year = $expiration_year;
		$this->security_code = $security_code;
		$this->street_address = $street_address;
		$this->address2 = $address2;
		$this->city = $city;
		$this->state = $state;
		$this->zip_code = $zip_code;
		$this->country = $country;
	}
}

?>