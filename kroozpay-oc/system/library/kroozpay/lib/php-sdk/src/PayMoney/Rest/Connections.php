<?php
namespace PayMoney\Rest;

class Connections {
	public function __construct() {
		!defined('BASE_URL') ? define('BASE_URL', 'https://kroozpay.com/gateway/') : false; 
	}

	public function execute( $url, $method, $payload, $headers = null) {
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
		));
		if (strtoupper($method) === 'POST') {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		}
		if (null !== $headers) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		$result = curl_exec($ch);
		$info   = curl_getinfo($ch, CURLINFO_HEADER_OUT);
		curl_close($ch);
		return $result;
	}
}
