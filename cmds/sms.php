<?php

/**
 * Apretaste SMS Service
 *
 * @param unknown $robot
 * @param string $from
 * @param string $argument
 * @param string $body
 * @param array $images
 * @return array
 */
function cmd_sms($robot, $from, $argument, $body = '', $images = array()){
	$body = str_replace("\n", " ", $body);
	$body = str_replace("\r", " ", $body);
	$body = str_replace("  ", " ", $body);
	
	$codes = ApretasteSMS::getCountryCodes();
	
	asort($codes);
	
	$as_plain_text = false;
	
	if (strpos($from, '@nauta.cu') !== false)
		$as_plain_text = true;
	
	$argument = trim($argument);
	
	if (strtolower($argument) == 'codigos') {
		return array(
				"answer_type" => "sms_codes",
				"codes" => $codes,
				"as_plain_text" => $as_plain_text
		);
	}
	
	if (! Apretaste::isUTF8($body))
		$body = utf8_encode($body);
	
	$body = quoted_printable_decode($body);
	$body = trim(strip_tags($body));
	$body = htmlentities($body);
	$body = str_replace('&', '', $body);
	$body = str_replace('tilde;', '', $body);
	$body = str_replace('acute;', '', $body);
	$body = html_entity_decode($body);
	
	$p = strrpos($body, "--");
	
	if ($p !== false)
		$body = substr($body, 0, $p);
	
	$body = trim($body);
	
	if (trim($body) == '')
		return array(
				"answer_type" => "sms_empty_text",
				"number" => $argument,
				"as_plain_text" => $as_plain_text
		);
		
		// Get country code
	$parts = ApretasteSMS::splitNumber($argument);
	
	if ($parts === false) {
		
		return array(
				"answer_type" => "sms_wrong_number",
				"number" => $argument,
				"message" => $body,
				"codes" => $codes,
				"credit" => ApretasteMoney::getCreditOf($from),
				"as_plain_text" => $as_plain_text
		);
	}
	
	$code = $parts['code'];
	$number = $parts['number'];
	
	// Split message
	$msg = trim($body);
	
	// $parts = ApretasteSMS::chopText($msg);
	// $tparts = count($parts);
	$parts = array(
			substr($body, 0, 160)
	);
	$tparts = 1;
	
	// Get rate
	$discount = ApretasteSMS::getRate($code);
	
	// Verify credit
	$credit = ApretasteMoney::getCreditOf($from);
	
	if ($credit < $discount * $tparts) {
		// no credit
		return array(
				"answer_type" => "sms_not_enought_funds",
				"credit" => $credit,
				"discount" => $discount * $tparts,
				"smsparts" => $parts,
				"as_plain_text" => $as_plain_text
		);
	}
	
	// Send message
	
	foreach ( $parts as $i => $part ) {
		$robot->log("Sending sms part $i - $part to $code - $number");
		ApretasteSMS::send($code, $number, $from, $part, $discount);
	}
	
	if (strlen($body) > 160) {
		$body = substr($body, 160);
	} else
		$body = false;
	
	$newcredit = ApretasteMoney::getCreditOf($from);
	
	return array(
			"answer_type" => "sms_sended",
			"credit" => $credit,
			"newcredit" => $newcredit,
			"discount" => $discount,
			"smsparts" => $parts,
			"bodyextra" => $body,
			"totaldiscount" => $discount * $tparts,
			"as_plain_text" => $as_plain_text
	);
}
	
