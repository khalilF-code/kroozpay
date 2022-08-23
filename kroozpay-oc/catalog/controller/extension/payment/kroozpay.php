<?php

$dir = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
$autoload = $dir . '/system/library/kroozpay/lib/vendor/autoload.php';
require $autoload;
use PayMoney\Api\Amount;
use PayMoney\Api\Payer;
use PayMoney\Api\Payment;
use PayMoney\Api\RedirectUrls;
use PayMoney\Api\Transaction;

class ControllerExtensionPaymentKroozpay extends Controller {

	public function index() {
		
		$this->load->language('extension/payment/kroozpay'); 
		$data['button_confirm'] = $this->language->get('button_confirm');

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
		$data['LogActive'] = $this->config->get('kroozpay_log');

		$order_id = $this->session->data['order_id'];

		$total = $this->currency->format($order_info['total'], $order_info['currency_code'], false, false);

		$transaction_amount = number_format( (float) $total, 2, '.', '' );
		$succsesUrl = $this->url->link('extension/payment/kroozpay/callback', '', 'SSL').'?order_id='.$order_id.'&token=';
        $cancelUrl = $this->url->link('checkout/failure');

		$payer = new Payer();
		$payer->setPaymentMethod('Kroozpay');

		$amountIns = new Amount();
		$amountIns->setTotal($transaction_amount)->setCurrency($order_info['currency_code']); 

		$trans = new Transaction();
		$trans->setAmount($amountIns);

		$urls = new RedirectUrls();
		$urls->setSuccessUrl($succsesUrl)->setCancelUrl($cancelUrl);

		$payment = new Payment();
		$payment->setCredentials([ 
					'client_id'     => $this->config->get('kroozpay_api'), 
					'client_secret' => $this->config->get('kroozpay_key'), 
				])
				->setRedirectUrls($urls)
				->setPayer($payer)
				->setTransaction($trans);
				
		try {
					
		    $payment->create(); //create payment
			$data['action']  = $payment->getApprovedUrl();
				
		} catch (\Exception $ex) {
				
	        echo 'Sorry We having problem connect to KroozPay Payments Error Message :'.$ex->getMessage();
		}		


		return $this->load->view('extension/payment/kroozpay', $data);
	}

	
	public function callback() {

		$this->load->model('checkout/order');
		$api = $this->config->get('kroozpay_api');
		$key = $this->config->get('kroozpay_key');
		$logActive = $this->config->get('Kroozpay_log');
		$canceled  = $this->config->get('Kroozpay_canceled');
		$complete  = $this->config->get('kroozpay_complete');

	    if (isset($this->request->get['order_id']) && isset($this->request->get['token'])) {
            $order_id = $this->request->get['order_id'];
            $token = $this->request->get['token'];
            $order = $this->model_checkout_order->getOrder($order_id);
			$encoded = json_encode(filter_var($token, FILTER_SANITIZE_STRING));
			$encoded = substr($encoded, 1);
			$decoded = json_decode(base64_decode($encoded), true);
					
			if (200 === $decoded['status']) {
	            $this->createLog("Kroozpay : Success Transaction.".serialize($decoded),$logActive);
				$this->model_checkout_order->addOrderHistory($order_id, $complete);
				$this->response->redirect($this->url->link('checkout/success'));
			} else {
				$this->createLog("Kroozpay :the payment failed Status .".serialize($decoded),$logActive);
		        $this->response->redirect($this->url->link('checkout/failure'));
			}
			
		}
				
		$this->response->redirect($this->url->link('checkout/failure'));
				
		exit;
	}


	public function createLog($text,$active) {
		if($active == 'Yes'){
			// Log
			$logfilename = __DIR__.'/kroozpay.log';
			$fp = @fopen($logfilename, 'a+');
			if ($fp) {
				fwrite($fp, date('M d Y G:i:s') . ' -- ' . $text . "\r\n");
				fclose($fp);
			}
		}
	}
}
?>