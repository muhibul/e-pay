<?php

// 1. Autoload the SDK Package. This will include all the files and classes to your autoloader
require 'vendor/autoload.php';

Braintree_Configuration::environment('sandbox');
Braintree_Configuration::merchantId('j8zgt44rd2m6dt74');
Braintree_Configuration::publicKey('w6myqf4pd67bhn8v');
Braintree_Configuration::privateKey('d2a87ea92ccf38881109be930c07e7da');

// $clientToken = Braintree_ClientToken::generate();
//echo '<pre>';print_r($clientToken);echo '</pre>';

$customer_info = Braintree_Customer::create([
    'firstName' => 'Mike',
    'lastName' => 'Jones',
    'company' => 'Jones Co.',
    'email' => 'mike.jones@example.com',
    'phone' => '281.330.8004',
    'fax' => '419.555.1235',
    'website' => 'http://example.com',
    'paymentMethodNonce' => nonceFromTheClient
]);
$customer_info->success;
$customer_id = $customer_info->customer->id;
$clientToken = Braintree_ClientToken::generate([
    "customerId" => $customer_id
]);
echo '<pre>';print_r($clientToken);echo '</pre>';//exit();

// $payment_method_nonce = Braintree_PaymentMethodNonce::create($customer_info); //A_PAYMENT_METHOD_TOKEN
// $nonce = $payment_method_nonce->paymentMethodNonce->nonce;
// echo '<pre>';print_r($payment_method_nonce);echo '</pre>';//exit();

$payment_method = Braintree_PaymentMethod::create([
    'customerId' => $customer_id,
    'paymentMethodNonce' => $payment_method_nonce
]);
echo '<pre>';print_r($payment_method);echo '</pre>';exit();



$transaction = Braintree_Transaction::saleNoValidate(array(
    'amount' => '100.00',
    'creditCard' => array(
        'cardholderName' => 'Muhibul Hasan',
        'number' => '5105105105105100',
        'expirationDate' => '05/12',
        'cvv' => '123',
    ),
    'customer' => array(
        'firstName' => 'Dan',
        'lastName' => 'Smith',
        'company' => 'Braintree',
        'email' => 'dan@example.com',
        'phone' => '419-555-1234',
        'fax' => '419-555-1235',
        'website' => 'http://braintreepayments.com'
    ),
));
echo '<pre>';print_r($transaction);echo '</pre>';

// $result = Braintree_Transaction::sale([
//     'amount' => '1000.00',
//     'paymentMethodNonce' => 'nonceFromTheClient',
//     'options' => [ 'submitForSettlement' => true ]
// ]);

if ($result->success) {
    print_r("success!: " . $result->transaction->id);
} else if ($result->transaction) {
    print_r("Error processing transaction:");
    print_r("\n  code: " . $result->transaction->processorResponseCode);
    print_r("\n  text: " . $result->transaction->processorResponseText);
} else {
    print_r("Validation errors: \n");
    print_r($result->errors->deepAll());
}

exit();

function braintree_text_field($label, $name, $result) {
    echo('<div>' . $label . '</div>');
    $fieldValue = isset($result) ? $result->valueForHtmlField($name) : '';
    echo('<div><input type="text" name="' . $name .'" value="' . $fieldValue . '" /></div>');
    $errors = isset($result) ? $result->errors->onHtmlField($name) : array();
    foreach($errors as $error) {
        echo('<div style="color: red;">' . $error->message . '</div>');
    }
    echo("\n");
}
?>
 
<html>
    <head>
        <title>Braintree Transparent Redirect</title>
    </head>
    <body>
        <?php
        if (isset($_GET["id"])) {
            $result = Braintree_TransparentRedirect::confirm($_SERVER['QUERY_STRING']);
        }
        if (isset($result) && $result->success) { ?>
            <h1>Braintree Transparent Redirect Response</h1>
            <?php $transaction = $result->transaction; ?>
            <table>
                <tr><td>transaction id</td><td><?php echo htmlentities($transaction->id); ?></td></tr>
                <tr><td>transaction status</td><td><?php echo htmlentities($transaction->status); ?></td></tr>
                <tr><td>transaction amount</td><td><?php echo htmlentities($transaction->amount); ?></td></tr>
                <tr><td>customer first name</td><td><?php echo htmlentities($transaction->customerDetails->firstName); ?></td></tr>
                <tr><td>customer last name</td><td><?php echo htmlentities($transaction->customerDetails->lastName); ?></td></tr>
                <tr><td>customer email</td><td><?php echo htmlentities($transaction->customerDetails->email); ?></td></tr>
                <tr><td>credit card number</td><td><?php echo htmlentities($transaction->creditCardDetails->maskedNumber); ?></td></tr>
                <tr><td>expiration date</td><td><?php echo htmlentities($transaction->creditCardDetails->expirationDate); ?></td></tr>
            </table>
        <?php
        } else {
            if (!isset($result)) { $result = null; } ?>
            <h1>Braintree Transparent Redirect Example</h1>
            <?php if (isset($result)) { ?>
                <div style="color: red;"><?php echo $result->errors->deepSize(); ?> error(s)</div>
            <?php } ?>
            <form method="POST" action="<?php echo Braintree_TransparentRedirect::url() ?>" autocomplete="off">
                <fieldset>
                    <legend>Customer</legend>
                    <?php braintree_text_field('First Name', 'transaction[customer][first_name]', $result); ?>
                    <?php braintree_text_field('Last Name', 'transaction[customer][last_name]', $result); ?>
                    <?php braintree_text_field('Email', 'transaction[customer][email]', $result); ?>
                </fieldset>
 
                <fieldset>
                    <legend>Payment Information</legend>
 
                    <?php braintree_text_field('Credit Card Number', 'transaction[credit_card][number]', $result); ?>
                    <?php braintree_text_field('Expiration Date (MM/YY)', 'transaction[credit_card][expiration_date]', $result); ?>
                    <?php braintree_text_field('CVV', 'transaction[credit_card][cvv]', $result); ?>
                </fieldset>
 
                <?php $tr_data = Braintree_TransparentRedirect::transactionData(
                    array('redirectUrl' => "http://" . $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH),
                    'transaction' => array('amount' => '10.00', 'type' => 'sale'))) ?>
                <input type="hidden" name="tr_data" value="<?php echo $tr_data ?>" />
 
                <br />
                <input type="submit" value="Submit" />
            </form>
        <?php } ?>
    </body>
</html>






