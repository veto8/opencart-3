<?php
/**
 * Class Pickup
 *
 * @package Admin\Controller\Extension\Shipping
 */
class ControllerExtensionShippingPickup extends Controller {
	/**
	 * @var array<string, string>
	 */
	private array $error = [];

	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('extension/shipping/pickup');

		$this->document->setTitle($this->language->get('heading_title'));

		// Settings
		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('shipping_pickup', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/shipping/pickup', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['action'] = $this->url->link('extension/shipping/pickup', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true);

		if (isset($this->request->post['shipping_pickup_geo_zone_id'])) {
			$data['shipping_pickup_geo_zone_id'] = (int)$this->request->post['shipping_pickup_geo_zone_id'];
		} else {
			$data['shipping_pickup_geo_zone_id'] = (int)$this->config->get('shipping_pickup_geo_zone_id');
		}

		// Geo Zones
		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['shipping_pickup_status'])) {
			$data['shipping_pickup_status'] = $this->request->post['shipping_pickup_status'];
		} else {
			$data['shipping_pickup_status'] = $this->config->get('shipping_pickup_status');
		}

		if (isset($this->request->post['shipping_pickup_sort_order'])) {
			$data['shipping_pickup_sort_order'] = $this->request->post['shipping_pickup_sort_order'];
		} else {
			$data['shipping_pickup_sort_order'] = $this->config->get('shipping_pickup_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/shipping/pickup', $data));
	}

	/**
	 * Validate
	 *
	 * @return bool
	 */
	protected function validate(): bool {
		if (!$this->user->hasPermission('modify', 'extension/shipping/pickup')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
