<?php

// autoload_psr4.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'Test\\' => array($baseDir . '/tests'),
    'Symfony\\Component\\Yaml\\' => array($vendorDir . '/symfony/yaml'),
    'Braintree\\' => array($baseDir . '/lib/Braintree', $vendorDir . '/braintree/braintree_php/lib/Braintree'),
);
