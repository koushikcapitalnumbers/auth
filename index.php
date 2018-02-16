$payment_mode = "test-live"; 
require_once('payment/config.php');
require_once('payment/class.creditcard.php');
require_once('payment/class.transaction_response.php');
require_once('payment/class.subscription_response.php');
require_once('payment/class.payment.php');
$InvoiceNumber ='sdfsdfdsf';


$payment = new Payment($InvoiceNumber, $payment_mode, 'text'); // refer to payment/class.payment.php
$credit_card = new CreditCard(Fname, lname, CC no, CCExp Month, CC Exp year, CC cvv, bill address, bill address 2, city, state,  zipcode, country);
$description = 'description';
$amount = 10.00
$transaction_response = $payment->ProcessSinglePayment($description, email, phone, $amount, $credit_card);
