<?php

/**
 * @subpackage Paymant plugin for Roskassa
 * @copyright 2005-2021  Syntlex Dev
 * @author  Syntlex Dev https://syntlex.info
 * @Product	: Paymant plugin for Roskassa
 * @Date	: 24 February 2021
 * @Contact	: cmsmodulsdever@gmail.com
 * @Licence	: GNU General Public License
 * This plugin is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2 (GPLv2) of the License, or (at your option) any later version.
 *
 * This plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 *  without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * See the GNU General Public License for more details <http://www.gnu.org/licenses/>.
 *
 **/


if (!defined('BOOTSTRAP')) 

{ 

	die('Access denied');

}


if (defined('PAYMENT_NOTIFICATION')) 

{

	$pp_response = array();

    

	if ($mode == 'notify' && !empty($_REQUEST["order_id"]))

	{

		if (isset($_REQUEST["id"]) && isset($_REQUEST["sign"]))

		{

			$err = false;

			$message = '';

			$order_id = $_REQUEST["order_id"];

			$order_info = fn_get_order_info($order_id);



			if (empty($processor_data))

			{

				$processor_data = fn_get_processor_data($order_info['payment_id']);

			}



			// запись логов



            $log_text =

                "--------------------------------------------------------\n" .

                "id you	shoop   	" . $_REQUEST["shop_id"] . "\n" .

                "amount				" . $_REQUEST["amount"] . "\n" .

                "kassa operation id " . $_REQUEST["id"] . "\n" .

                "mercant order id	" . $_REQUEST["order_id"] . "\n" .

                "e-mail client		" . $_REQUEST["email"] . "\n" .

                "currency			" . $_REQUEST["currency"] . "\n" .

                "sign				" . $_REQUEST["sign"] . "\n\n";



			$log_file = $processor_data['processor_params']['pathlog'];



			if (!empty($log_file)) {



				file_put_contents($_SERVER['DOCUMENT_ROOT'] . $log_file, $log_text, FILE_APPEND);

			}



			// проверка цифровой подписи и ip



			$data = $_POST;

			unset($data['sign']);

			ksort($data);

			$str = http_build_query($data);

			$sign_hash = md5($str . $processor_data['processor_params']['m_key1']);



			if (!$err) {



				$order_amount = number_format($order_info['total'], 2, '.', '');



				// проверка суммы и валюты



				if ($_REQUEST["amount"] != $order_amount)

				{

					$message .= __("payeer_mail_msg7") . "\n";

					$err = true;

				}



				// проверка статуса



				if (!$err) {



					if ($_REQUEST["sign"] == $sign_hash) {





                        if (fn_check_payment_script('roskassa.php', $order_id)) {



                            $pp_response['order_status'] = 'C';

                            $pp_response['reason_text'] = __('transaction_approved');

                            $pp_response['transaction_id'] = $_REQUEST["id"];

                            fn_finish_payment($order_id, $pp_response);

                            exit('YES');

                        }

                    }

                    else {

                        $message .= __("roskassa_mail_msg2") . "\n";

                        $pp_response['order_status'] = 'D';

                        $pp_response['reason_text'] = __('text_transaction_declined');

                        $pp_response['transaction_id'] = $_REQUEST["id"];

                        fn_finish_payment($order_id, $pp_response);

                        $err = true;

                    }

				}

			}



			

			if ($err)

			{

				$to = $processor_data['processor_params']['emailerr'];



				if (!empty($to))

				{

					$message = __("roskassa_mail_msg1") . ":\n\n" . $message . "\n" . $log_text;

					$headers = "From: no-reply@" . $_SERVER['HTTP_HOST'] . "\r\n" . 

					"Content-type: text/plain; charset=utf-8 \r\n";

					mail($to, __("roskassa_mail_subject"), $message, $headers);

				}

				

				exit($order_id . '|error|' . $message);

			}

		}

	}



    if ($mode == 'success' && !empty($_REQUEST["order_id"])) {

		if (isset($_REQUEST["id"])) {

			$pp_response['order_status'] = 'P';

			$pp_response['reason_text'] = __('transaction_approved');

			$pp_response['transaction_id'] = $_REQUEST["id"];

		}

	}

	elseif ($mode == 'fail' && !empty($_REQUEST["order_id"]))

	{

			$pp_response['order_status'] = 'F';

			$pp_response['reason_text'] = __('text_transaction_declined');

	}

	

	$order_id = $_REQUEST["order_id"];

	$order_info = fn_get_order_info($order_id);

	if ($order_info['status'] == 'C' || $order_info['status'] == 'D')

	{

		$pp_response['order_status'] = $order_info['status'];

		fn_order_placement_routines('route', $order_id);

	}

	elseif (fn_check_payment_script('roskassa.php', $order_id))

	{

		fn_order_placement_routines('route', $order_id);

        fn_finish_payment($order_id, $pp_response);

    }

} 

else 

{

	$payment_url = $processor_data['processor_params']['m_url'];

	$m_shop = $processor_data['processor_params']['m_shop'];

	$m_orderid = $order_id;

	$m_amount = fn_format_price($order_info['total'], 'RUB');

	$m_amount = number_format($m_amount, 2, '.', '');

	$m_key = $processor_data['processor_params']['m_key1'];

	$arHash = array(

			'shop_id' => $m_shop,

      'amount' => $m_amount,

      'currency' => $processor_data['processor_params']['currency'],

      'order_id' => $m_orderid,

      //'test' => 1 

	);

	ksort($arHash);

	$str = http_build_query($arHash);

	$sign = md5($str . $m_key);

	$post_data = array(

		'shop_id' => $m_shop,

		'amount' => $m_amount,

		'currency' => $processor_data['processor_params']['currency'],

    'order_id' => $m_orderid,

    'sign' => $sign,

    //'test' => 1

	);

	$i = 0;
	foreach ($order_info['products'] as $value) {
		$post_data['receipt[items]['.$i.'][name]'] = $value['product'];
		$post_data['receipt[items]['.$i.'][count]'] = $value['amount'];
		$post_data['receipt[items]['.$i.'][price]'] = $value['price'];
		$i++;
	}

	if ($order_info['shipping_cost'] > 0)
		$cost = $order_info['shipping_cost'] - $order_info['subtotal_discount'];

	if (!empty($cost)) { 
		$post_data['receipt[items]['.$i.'][name]'] = 'Доставка';
		$post_data['receipt[items]['.$i.'][count]'] = 1;
		$post_data['receipt[items]['.$i.'][price]'] = $cost;
	}

	fn_create_payment_form($payment_url, $post_data, 'roskassa', true, 'get');

}

exit;