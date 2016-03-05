<?php

// 1. Autoload the SDK Package. This will include all the files and classes to your autoloader
require __DIR__  . '/vendor/autoload.php';

class Test extends CI_Controller
{
    public function index()
    {
        $browser = new Buzz\Browser();
        $response = $browser->get('http://www.google.com');

        echo $browser->getLastRequest()."\n";
        echo $response;
    }
}

// 2. Provide your Secret Key. Replace the given one with your app clientId, and Secret
// https://developer.paypal.com/webapps/developer/applications/myapps
$apiContext = new \PayPal\Rest\ApiContext(
    new \PayPal\Auth\OAuthTokenCredential(
        'AYVF6hJjeSpJYW59Wux664s9fD7IQ4wtIYJ61l2740oYFx4v2NTHkTmsMEPStjCHqgoBT_Bt6ZEEY1js',     // ClientID
        'EC2Q6W7WFeBjNXamsTpct2H54WqOQsDzAmzh4vBfDWv0PvcqZX6marfxBf-7q-XiHSzwC9LkktOdS6Ud'      // ClientSecret
    )
);

// 3. Lets try to save a credit card to Vault using Vault API mentioned here
// https://developer.paypal.com/webapps/developer/docs/api/#store-a-credit-card
$creditCard = new \PayPal\Api\CreditCard();
$creditCard->setType("visa")
    ->setNumber("4417119669820331")
    ->setExpireMonth("11")
    ->setExpireYear("2019")
    ->setCvv2("012")
    ->setFirstName("Joe")
    ->setLastName("Shopper");

// 4. Make a Create Call and Print the Card
try {
    $creditCard->create($apiContext);
    //echo $creditCard;
    echo '<pre>';print_r($creditCard);echo '</pre>';
}
catch (\PayPal\Exception\PayPalConnectionException $ex) {
    // This will print the detailed information on the exception. 
    //REALLY HELPFUL FOR DEBUGGING
    echo $ex->getData();
}
