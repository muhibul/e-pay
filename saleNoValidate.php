<?php

// 1. Autoload the SDK Package. This will include all the files and classes to your autoloader
require 'vendor/autoload.php';

Braintree_Configuration::environment('sandbox');
Braintree_Configuration::merchantId('j8zgt44rd2m6dt74');
Braintree_Configuration::publicKey('w6myqf4pd67bhn8v');
Braintree_Configuration::privateKey('d2a87ea92ccf38881109be930c07e7da');

$transaction = Braintree_Transaction::saleNoValidate(array(
    'amount' => '100.00',
    'merchantAccountId' => 'accept_thb',
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
