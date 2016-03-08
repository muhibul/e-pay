# e-pay
Demo for online payment system using Paypal REST API and Braintree Payment Gateway

## Configuration
1. Go to 'application/config/config.php' and change the line if necessary: $config['base_url'] = 'http://localhost/e-pay/'; 
2. Run the sql file 'epay.sql'. It will create a database 'epay' and 2 tables.
3. Go to 'application/config/database.php' and change username, password and database in line 79, 80, 81.
4. If you are using mac or linux environtment, please make sure 'vendor' directory has read-write permission enabled.

## Answer to the question "How would I handle security for saving credit cards?"

* I will use 'volt' offered by payment gateways to store credit card information
* After that I will save the record_id/CardId from the volt in my database.

This will help me not to be worried about the security of credit card
