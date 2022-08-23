<?php 

class ControllerExtensionPaymentKroozpay extends Controller {

	private $error = array(); 
 
	public function index() {
		
		$this->load->language('extension/payment/kroozpay');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		
		$data['breadcrumbs'] = array();
		$tokenName="token";
		$redirDest="extension/extension";

		if (array_key_exists("user_token",$this->session->data)) {
			$tokenName="user_token";
			$redirDest="marketplace/extension";
		}
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate() ) {
			$this->model_setting_setting->editSetting('payment_kroozpay', $this->request->post);
			///
			$this->session->data['success'] = $this->language->get('text_success');
			
			$this->response->redirect($this->url->link($redirDest, $tokenName.'=' . $this->session->data[$tokenName], 'SSL'));
		}
		
		$data['heading_title'] = $this->language->get('heading_title');
	
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['text_payment'] = $this->language->get('text_payment');
		$data['text_defered'] = $this->language->get('text_defered');
		$data['text_authenticate'] = $this->language->get('text_authenticate');

		$data['text_all_zones'] = $this->language->get('text_all_zones');
		
		$data['entry_api'] = $this->language->get('entry_api');
		$data['entry_key'] = $this->language->get('entry_key');

		$data['entry_log'] = $this->language->get('entry_log');

		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['entry_complete'] = $this->language->get('entry_complete');
		$data['entry_canceled'] = $this->language->get('entry_canceled');

		$this->load->model('localisation/order_status');

		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		// FIN DE ERRORES
		
		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', $tokenName.'=' . $this->session->data[$tokenName], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', $tokenName.'=' . $this->session->data[$tokenName], 'SSL')      		
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/payment/kroozpay', $tokenName.'=' . $this->session->data[$tokenName], 'SSL')
		);
		
		$data['action'] = $this->url->link('extension/payment/kroozpay', $tokenName.'=' . $this->session->data[$tokenName], 'SSL');

		$data['cancel'] = $this->url->link('extension/extension', $tokenName.'=' . $this->session->data[$tokenName], 'SSL');
		
		
		//RECOGIDA DE PARAM.
		


		if (isset($this->request->post['kroozpay_api'])) {
			$data['kroozpay_api'] = $this->request->post['kroozpay_api'];
		} else {
			$data['kroozpay_api'] = $this->config->get('kroozpay_api');
		}

		if (isset($this->request->post['kroozpay_key'])) {
			$data['kroozpay_key'] = $this->request->post['kroozpay_key'];
		} else {
			$data['kroozpay_key'] = $this->config->get('kroozpay_key');
		}


	     if (isset($this->request->post['kroozpay_complete'])) {
			$data['kroozpay_complete'] = $this->request->post['kroozpay_complete'];
		} else {
			$data['kroozpay_complete'] = $this->config->get('kroozpay_complete');
		}

		if (isset($this->request->post['kroozpay_canceled'])) {
			$data['kroozpay_canceled'] = $this->request->post['kroozpay_canceled'];
		} else {
			$data['kroozpay_canceled'] = $this->config->get('kroozpay_canceled');
		}
		if (isset($this->request->post['kroozpay_log'])) {
			$data['kroozpay_log'] = $this->request->post['kroozpay_log'];
		} else {
			$data['kroozpay_log'] = $this->config->get('kroozpay_log');
		}
		

		if (isset($this->request->post['kroozpay_status'])) {
			$data['kroozpay_status'] = $this->request->post['kroozpay_status'];
		} else {
			$data['kroozpay_status'] = $this->config->get('kroozpay_status');
		}
		
		if (isset($this->request->post['payment_kroozpay_status'])) {
			$data['payment_kroozpay_status'] = $this->request->post['payment_kroozpay_status'];
		} else {
			$data['payment_kroozpay_status'] = $this->config->get('payment_kroozpay_status');
		}

		if (isset($this->request->post['kroozpay_order_status_id'])) {
			$data['kroozpay_order_status_id'] = $this->request->post['kroozpay_order_status_id'];
		} else {
			$data['kroozpay_order_status_id'] = $this->config->get('kroozpay_order_status_id'); 
		} 

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		
		if (isset($this->request->post['kroozpay_sort_order'])) {
			$data['kroozpay_sort_order'] = $this->request->post['kroozpay_sort_order'];
		} else {
			$data['kroozpay_sort_order'] = $this->config->get('kroozpay_sort_order');
		}
	

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		
		//FIN DE RECOGIDA DE PARAMS.


		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/kroozpay', $data));
 
	}
	private function validate() {
			
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	

	}
}
?>