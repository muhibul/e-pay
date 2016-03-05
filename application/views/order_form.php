<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Online payment deomo</title>
	<script src="<?php echo site_url('../js/braintree-2.21.0.min.js'); ?>"></script>
    <script src="<?php echo site_url('../js/jquery.min.js'); ?>"></script>
    <script src="<?php echo site_url('../js/jquery.creditCardValidator.js'); ?>"></script>
    <script type="text/javascript">
    	$(window).bind("pageshow", function() {
		    var form = $('form'); 
		    // let the browser natively reset defaults
		    form[0].reset();
		});
    	$(function() {
    		var result = $('#cc_number').validateCreditCard(function (result) {
	    		if(result.card_type.name != ''){
	    			$('#cc_type').val(result.card_type.name);
	    		}
	    		
	    		//console.log(result);
	    		//result.length_valid
	    		//result.luhn_valid
	    	});
    	});
    	/*var bt_url = '<?php echo $url; ?>';
    	var pp_url = $('#braintree_custom_form').attr('action');
    	$('#cur').change(function(){
    		var cur = $(this).val();
    		if(cur != 'USD' || cur != 'EUR' || cur != 'AUD'){
    			$('#braintree_custom_form').attr('action', bt_url);
    		}else{
    			$('#braintree_custom_form').attr('action', pp_url);
    		}
    		
    	});*/
    </script>

	<style type="text/css">

	::selection { background-color: #E13300; color: white; }
	::-moz-selection { background-color: #E13300; color: white; }

	body {
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}

	a {
		color: #003399;
		background-color: transparent;
		font-weight: normal;
	}

	h2 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
	}

	code {
		font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 12px;
		background-color: #f9f9f9;
		border: 1px solid #D0D0D0;
		color: #002166;
		display: block;
		margin: 14px 0 14px 0;
		padding: 12px 10px 12px 10px;
	}

	#body {
		margin: 0 15px 0 15px;
	}

	p.footer {
		text-align: right;
		font-size: 11px;
		border-top: 1px solid #D0D0D0;
		line-height: 32px;
		padding: 0 10px 0 10px;
		margin: 20px 0 0 0;
	}

	#container {
		margin: 10px;
		border: 1px solid #D0D0D0;
		box-shadow: 0 0 8px #D0D0D0;
	}
	.error p{color: red;}
	</style>
</head>
<body>

<div id="container">
	<?php
		$form_attributes = array('id' => 'braintree_custom_form','autocomplete' => 'off');
		echo form_open('epay/process', $form_attributes);
	?>
	<h2>Order</h1>
<div id="payment-form"></div>
	<div id="body">
		<div class="error">
		<?php echo validation_errors(); ?>
		</div>

		<p>Currency:</p>
		<p>
		<?php
		$options = array(
	        'USD' => 'USD',
	        'EUR' => 'EUR',
	        'THB' => 'THB',
	        'HKD' => 'HKD',
	        'SGD' => 'SGD',
	        'AUD' => 'AUD',
		);
		$attr = array(
	        'name'  => 'cur',
	        'id'    => 'cur',
	        //'onChange' => ''
		);
		$cur_ddl_js = '';
		echo form_dropdown($attr, $options, 0, $cur_ddl_js);
		?>
		</p>

		<p>Price:</p>
		<p>
		<?php
		$data = array(
	        'type'  => 'text',
	        'name'  => 'transaction[amount]',
	        'id'    => 'price',
	        //'value' => set_value('price'),
	        'autocomplete' => 'off'
		);
		echo form_input($data);
		?>
		</p>

		<p>Full Name:</p>
		<p>
		<?php
		$data = array(
	        'type'  => 'text',
	        'name'  => 'full_name',
	        'id'    => 'full_name',
	        //'value' => set_value('full_name'),
	        'autocomplete' => 'off'
		);
		echo form_input($data);
		?>
		</p>
	</div>

	<h2>Payment</h1>

	<div id="body">
		<p>Credit card holder name:</p>
		<p>
		<?php
		$data = array(
	        'type'  => 'text',
	        'name'  => 'transaction[credit_card][cardholder_name]',
	        'id'    => 'cc_holder_name',
	        //'value' => set_value('cc_holder_name'),
	        'autocomplete' => 'off'
		);
		echo form_input($data);
		?>
		</p>

		<p>Credit card number: (visa: 4148529247832259, amex: 378282246310005, mc: 5555555555554444)</p>
		<p>
		<?php
		$data = array(
	        'type'  => 'text',
	        'name'  => 'transaction[credit_card][number]',
	        'id'    => 'cc_number',
	        'maxlength' => '22',
	        //'value' => set_value('cc_number'),
	        'autocomplete' => 'off'
		);
		echo form_input($data);

		$data = array(
	        'type'  => 'hidden',
	        'name'  => 'cc_type',
	        'id'    => 'cc_type',
	        //'value' => set_value('cc_type')
		);
		echo form_input($data);
		?>
		</p>

		<p>Credit card expiration: (11/2019)</p>
		<p>
		<?php
		$attributes = array(
	        'name'  => 'transaction[credit_card][expiration_month]',
	        'id'    => 'cc_exp_month',
	        'autocomplete' => 'off'
		);
		$options = array(
	        '01' => '01',
	        '02' => '02',
	        '03' => '03',
	        '04' => '04',
	        '05' => '05',
	        '06' => '06',
	        '07' => '07',
	        '08' => '08',
	        '09' => '09',
	        '10' => '10',
	        '11' => '11',
	        '12' => '12',
		);
		echo form_dropdown($attributes, $options, set_value('cc_exp_month'));

		$data = array(
	        'type'  => 'text',
	        'name'  => 'transaction[credit_card][expiration_year]',
	        'id'    => 'cc_exp_yr',
	        //'value' => set_value('cc_exp_yr'),
	        'size' => '4',
	        'maxlength' => '4',
	        'autocomplete' => 'off'
		);
		echo form_input($data);
		?>
		</p>

		<p>Credit card CCV: (012)</p>
		<p>
		<?php
		$data = array(
	        'type'  => 'text',
	        'name'  => 'transaction[credit_card][cvv]',
	        'id'    => 'ccv',
	        //'value' => set_value('ccv'),
	        'size' => '4',
	        'maxlength' => '4',
	        'autocomplete' => 'off'
		);
		echo form_input($data);
		?>
		</p>

		<p>
		<input type="hidden" name="transaction[merchant_account_id]" id="merchant_account_id" value="wwwmuhibulcom" />
		<input type="hidden" name="tr_data" value="<?php echo $tr_data ?>" />
		<?php
		$data = array(
	        'name'          => 'pay',
	        'id'            => 'pay',
	        'value'         => 'Submit',
	        'type'          => 'submit',
	        //'content'       => 'Reset'
		);
		echo form_submit($data);
		?>
		</p>
	</div>
</div>
<script type="text/javascript">

	var bt_url = '<?php echo $url; ?>';
	var pp_url = $('#braintree_custom_form').attr('action');
	$('#cur').change(function(){
		var cur = $(this).val();
		if(cur == 'USD' || cur == 'EUR' || cur == 'AUD'){
			$('#braintree_custom_form').attr('action', pp_url);
			$('#merchant_account_id').val('wwwmuhibulcom');
		}else{
			$('#braintree_custom_form').attr('action', bt_url);
			if(cur == 'THB'){
				$('#merchant_account_id').val('accept_thb');
			}else if(cur == 'HKD'){
				$('#merchant_account_id').val('accept_hkd');
			}else if(cur == 'SGD'){
				$('#merchant_account_id').val('accept_sgd');
			}
		}
		
	});
	/*var clientToken = "<?php //echo $clientToken; ?>";

	braintree.setup(clientToken, "custom", {
        id: "braintree_custom_form",
        hostedFields: {
          number: {
            selector: "#cc_number"
          },
          cvv: {
            selector: "#ccv"
          },
          expirationDate: {
            selector: "#cc_exp_date"
          },
          expirationMonth: {
            selector: "#cc_exp_month"
          },
          expirationYear: {
            selector: "#cc_exp_yr"
          },
        }
    });*/
</script>
</body>
</html>