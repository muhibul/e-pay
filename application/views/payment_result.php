<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Online payment deomo</title>
	
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
	.error,.error p{color: red;}
	.success,.success p{color: green;}
	</style>
</head>
<body>

<div id="container">
	
	<h2>Payment Result</h1>

	<div id="body">
		<?php
			if($msg_type == 'error') 
				$class = 'error';
			else
				$class = 'success';
		?>
		<p class="<?php echo $class; ?>">
			<?php
				echo $msg;
			?>
		</p>

		<p>
			<?php
				if($msg_type == 'success'){
					if(isset($payment_id)) echo 'Payment ID: '.$payment_id.'<br>';
					if(isset($total)) echo 'Total: '.$total.'<br>';
					if(isset($currency)) echo 'Currency: '.$currency.'<br>';
					if(isset($subtotal)) echo 'Subtotal: '.$subtotal.'<br>';
					if(isset($description)) echo 'Description: '.$description.'<br>';

					foreach ($items as $item) {
						echo '<hr>';
						if(isset($item['invoice_number'])) echo 'Invoice Number: '.$item['invoice_number'].'<br>';
						if(isset($item['item_name'])) echo 'Item Name: '.$item['item_name'].'<br>';
						if(isset($item['price'])) echo 'Item Price: '.$item['price'].'<br>';
						if(isset($item['currency'])) echo 'Item Currency: '.$item['currency'].'<br>';
						if(isset($item['quantity'])) echo 'Item Quantity: '.$item['quantity'].'<br>';
						if(isset($item['description'])) echo 'Item Description: '.$item['description'].'<br>';
					}
					
				}
				//echo '<pre>';print_r($processed_data);echo '</pre>';
			?>
		</p>
	</div>
</div>

</body>
</html>