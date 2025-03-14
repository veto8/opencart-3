<?php
/**
 * Class Order
 *
 * Can be called using $this->load->model('sale/order');
 *
 * @package Admin\Model\Sale
 */
class ModelSaleOrder extends Model {
	/**
	 * Get Order
	 *
	 * @param int $order_id primary key of the order record
	 *
	 * @return array<string, mixed> order record that has order ID
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $order_info = $this->model_sale_order->getOrder($order_id);
	 */
	public function getOrder(int $order_id): array {
		$order_query = $this->db->query("SELECT *, (SELECT CONCAT(`c`.`firstname`, ' ', `c`.`lastname`) FROM `" . DB_PREFIX . "customer` `c` WHERE `c`.`customer_id` = `o`.`customer_id`) AS `customer`, (SELECT `os`.`name` FROM `" . DB_PREFIX . "order_status` `os` WHERE `os`.`order_status_id` = `o`.`order_status_id` AND `os`.`language_id` = '" . (int)$this->config->get('config_language_id') . "') AS `order_status` FROM `" . DB_PREFIX . "order` `o` WHERE `o`.`order_id` = '" . (int)$order_id . "'");

		if ($order_query->num_rows) {
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE `country_id` = '" . (int)$order_query->row['payment_country_id'] . "'");

			if ($country_query->num_rows) {
				$payment_iso_code_2 = $country_query->row['iso_code_2'];
				$payment_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$payment_iso_code_2 = '';
				$payment_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE `zone_id` = '" . (int)$order_query->row['payment_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$payment_zone_code = $zone_query->row['code'];
			} else {
				$payment_zone_code = '';
			}

			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE `country_id` = '" . (int)$order_query->row['shipping_country_id'] . "'");

			if ($country_query->num_rows) {
				$shipping_iso_code_2 = $country_query->row['iso_code_2'];
				$shipping_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$shipping_iso_code_2 = '';
				$shipping_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE `zone_id` = '" . (int)$order_query->row['shipping_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$shipping_zone_code = $zone_query->row['code'];
			} else {
				$shipping_zone_code = '';
			}

			$reward = 0;

			$order_product_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE `order_id` = '" . (int)$order_id . "'");

			foreach ($order_product_query->rows as $product) {
				$reward += $product['reward'];
			}

			// Customers
			$this->load->model('customer/customer');

			$affiliate_info = $this->model_customer_customer->getCustomer($order_query->row['affiliate_id']);

			if ($affiliate_info) {
				$affiliate_firstname = $affiliate_info['firstname'];
				$affiliate_lastname = $affiliate_info['lastname'];
			} else {
				$affiliate_firstname = '';
				$affiliate_lastname = '';
			}

			// Languages
			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($order_query->row['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}

			$payment_method = ($order_query->row['payment_method'] ? json_decode($order_query->row['payment_method'], true) : '');
			$shipping_method = ($order_query->row['shipping_method'] ? json_decode($order_query->row['shipping_method'], true) : '');

			return [
				'order_id'                => $order_query->row['order_id'],
				'invoice_no'              => $order_query->row['invoice_no'],
				'invoice_prefix'          => $order_query->row['invoice_prefix'],
				'store_id'                => $order_query->row['store_id'],
				'store_name'              => $order_query->row['store_name'],
				'store_url'               => $order_query->row['store_url'],
				'customer_id'             => $order_query->row['customer_id'],
				'customer'                => $order_query->row['customer'],
				'customer_group_id'       => $order_query->row['customer_group_id'],
				'firstname'               => $order_query->row['firstname'],
				'lastname'                => $order_query->row['lastname'],
				'email'                   => $order_query->row['email'],
				'telephone'               => $order_query->row['telephone'],
				'custom_field'            => json_decode($order_query->row['custom_field'], true),
				'payment_firstname'       => $order_query->row['payment_firstname'],
				'payment_lastname'        => $order_query->row['payment_lastname'],
				'payment_company'         => $order_query->row['payment_company'],
				'payment_address_1'       => $order_query->row['payment_address_1'],
				'payment_address_2'       => $order_query->row['payment_address_2'],
				'payment_postcode'        => $order_query->row['payment_postcode'],
				'payment_city'            => $order_query->row['payment_city'],
				'payment_zone_id'         => $order_query->row['payment_zone_id'],
				'payment_zone'            => $order_query->row['payment_zone'],
				'payment_zone_code'       => $payment_zone_code,
				'payment_country_id'      => $order_query->row['payment_country_id'],
				'payment_country'         => $order_query->row['payment_country'],
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'payment_address_format'  => $order_query->row['payment_address_format'],
				'payment_custom_field'    => json_decode($order_query->row['payment_custom_field'], true),
				'payment_method'          => $payment_method['name'],
				'payment_code'            => $payment_method['code'],
				'shipping_firstname'      => $order_query->row['shipping_firstname'],
				'shipping_lastname'       => $order_query->row['shipping_lastname'],
				'shipping_company'        => $order_query->row['shipping_company'],
				'shipping_address_1'      => $order_query->row['shipping_address_1'],
				'shipping_address_2'      => $order_query->row['shipping_address_2'],
				'shipping_postcode'       => $order_query->row['shipping_postcode'],
				'shipping_city'           => $order_query->row['shipping_city'],
				'shipping_zone_id'        => $order_query->row['shipping_zone_id'],
				'shipping_zone'           => $order_query->row['shipping_zone'],
				'shipping_zone_code'      => $shipping_zone_code,
				'shipping_country_id'     => $order_query->row['shipping_country_id'],
				'shipping_country'        => $order_query->row['shipping_country'],
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
				'shipping_address_format' => $order_query->row['shipping_address_format'],
				'shipping_custom_field'   => json_decode($order_query->row['shipping_custom_field'], true),
				'shipping_method'         => $shipping_method['name'],
				'comment'                 => $order_query->row['comment'],
				'total'                   => $order_query->row['total'],
				'reward'                  => $reward,
				'order_status_id'         => $order_query->row['order_status_id'],
				'order_status'            => $order_query->row['order_status'],
				'affiliate_id'            => $order_query->row['affiliate_id'],
				'affiliate_firstname'     => $affiliate_firstname,
				'affiliate_lastname'      => $affiliate_lastname,
				'commission'              => $order_query->row['commission'],
				'language_id'             => $order_query->row['language_id'],
				'language_code'           => $language_code,
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency_code'],
				'currency_value'          => $order_query->row['currency_value'],
				'ip'                      => $order_query->row['ip'],
				'forwarded_ip'            => $order_query->row['forwarded_ip'],
				'user_agent'              => $order_query->row['user_agent'],
				'accept_language'         => $order_query->row['accept_language'],
				'date_added'              => $order_query->row['date_added'],
				'date_modified'           => $order_query->row['date_modified']
			];
		} else {
			return [];
		}
	}

	/**
	 * Get Orders
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> order records
	 *
	 * @example
	 *
	 * $filter_data = [
	 *     'filter_order_id'        => 1,
	 *     'filter_customer_id'     => 1,
	 *     'filter_customer'        => 'John Doe',
	 *     'filter_store_id'        => 1,
	 *     'filter_order_status'    => 'Pending',
	 *     'filter_order_status_id' => 1,
	 *     'filter_total'           => 0.0000,
	 *     'filter_date_from'       => '2021-01-01',
	 *     'filter_date_to'         => '2021-01-31',
	 *     'sort'                   => 'o.order_id',
	 *     'order'                  => 'DESC',
	 *     'start'                  => 0,
	 *     'limit'                  => 10
	 * ];
	 *
	 * $this->load->model('sale/order');
	 *
	 * $results = $this->model_sale_order->getOrders($filter_data);
	 */
	public function getOrders(array $data = []): array {
		$sql = "SELECT `o`.`order_id`, CONCAT(`o`.`firstname`, ' ', `o`.`lastname`) AS `customer`, (SELECT `os`.`name` FROM `" . DB_PREFIX . "order_status` `os` WHERE `os`.`order_status_id` = `o`.`order_status_id` AND `os`.`language_id` = '" . (int)$this->config->get('config_language_id') . "') AS `order_status`, `o`.`total`, `o`.`currency_code`, `o`.`currency_value`, `o`.`date_added`, `o`.`date_modified` FROM `" . DB_PREFIX . "order` `o`";

		if (!empty($data['filter_order_status'])) {
			$implode = [];

			$order_statuses = explode(',', $data['filter_order_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "`o`.`order_status_id` = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} elseif (isset($data['filter_order_status_id']) && $data['filter_order_status_id'] !== '') {
			$sql .= " WHERE `o`.`order_status_id` = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE `o`.`order_status_id` > '0'";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND `o`.`order_id` = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(`o`.`firstname`, ' ', `o`.`lastname`) LIKE '" . $this->db->escape('%' . $data['filter_customer'] . '%') . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(`o`.`date_added`) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (!empty($data['filter_date_modified'])) {
			$sql .= " AND DATE(`o`.`date_modified`) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
		}

		if (!empty($data['filter_total'])) {
			$sql .= " AND `o`.`total` = '" . (float)$data['filter_total'] . "'";
		}

		$sort_data = [
			'o.order_id',
			'customer',
			'order_status',
			'o.date_added',
			'o.date_modified',
			'o.total'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `o`.`order_id`";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	/**
	 * Get Products
	 *
	 * @param int $order_id primary key of the order record
	 *
	 * @return array<int, array<string, mixed>> product records that have order ID
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $products = $this->model_sale_order->getProducts($order_id);
	 */
	public function getProducts(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE `order_id` = '" . (int)$order_id . "'");

		return $query->rows;
	}

	/**
	 * Get Product By Order Product ID
	 *
	 * @param int $order_id         primary key of the order record
	 * @param int $order_product_id primary key of the order product record
	 *
	 * @return array<string, mixed> product records that have order ID, order product ID
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $order_product = $this->model_sale_order->getProductByOrderProductId($order_id, $order_product_id);
	 */
	public function getProductByOrderProductId(int $order_id, int $order_product_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE `order_id` = '" . (int)$order_id . "' AND `order_product_id` = '" . (int)$order_product_id . "' ORDER BY `order_product_id` ASC");

		return $query->row;
	}

	/**
	 * Get Options
	 *
	 * @param int $order_id         primary key of the order record
	 * @param int $order_product_id primary key of the order product record
	 *
	 * @return array<int, array<string, mixed>> option records that have order ID, order product ID
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $order_options = $this->model_sale_order->getOptions($order_id, $order_product_id);
	 */
	public function getOptions(int $order_id, int $order_product_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_option` WHERE `order_id` = '" . (int)$order_id . "' AND `order_product_id` = '" . (int)$order_product_id . "'");

		return $query->rows;
	}

	/**
	 * Get Subscription
	 *
	 * @param int $order_id         primary key of the order record
	 * @param int $order_product_id primary key of the order product record
	 *
	 * @return array<string, mixed> subscription records that have order ID
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $order_subscription_info = $this->model_sale_order->getSubscription($order_id, $order_product_id);
	 */
	public function getSubscription(int $order_id, int $order_product_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_subscription` WHERE `order_id` = '" . (int)$order_id . "' AND `order_product_id` = '" . (int)$order_product_id . "'");

		return $query->row;
	}

	/**
	 * Get Vouchers
	 *
	 * @param int $order_id primary key of the order record
	 *
	 * @return array<int, array<string, mixed>> voucher records that have order ID
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $order_vouchers = $this->model_sale_order->getVouchers($order_id);
	 */
	public function getVouchers(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_voucher` WHERE `order_id` = '" . (int)$order_id . "'");

		return $query->rows;
	}

	/**
	 * Get Voucher By Voucher ID
	 *
	 * @param int $voucher_id voucher record that has voucher ID
	 *
	 * @return array<string, mixed>
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $order_voucher_info = $this->model_sale_order->getOrderVoucherByVoucherId($voucher_id);
	 */
	public function getOrderVoucherByVoucherId(int $voucher_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_voucher` WHERE `voucher_id` = '" . (int)$voucher_id . "'");

		return $query->row;
	}

	/**
	 * Get Totals
	 *
	 * @param int $order_id primary key of the order record
	 *
	 * @return array<int, array<string, mixed>> total records that have order ID
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $order_totals = $this->model_sale_order->getTotals($order_id);
	 */
	public function getTotals(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE `order_id` = '" . (int)$order_id . "' ORDER BY `sort_order`");

		return $query->rows;
	}

	/**
	 * Get Total Orders
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return int total number of order records
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $order_total = $this->model_sale_order->getTotalOrders();
	 */
	public function getTotalOrders(array $data = []): int {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order`";

		if (!empty($data['filter_order_status'])) {
			$implode = [];

			$order_statuses = explode(',', $data['filter_order_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "`order_status_id` = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} elseif (isset($data['filter_order_status_id']) && $data['filter_order_status_id'] !== '') {
			$sql .= " WHERE `order_status_id` = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE `order_status_id` > '0'";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND `order_id` = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(`firstname`, ' ', `lastname`) LIKE '" . $this->db->escape('%' . $data['filter_customer'] . '%') . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(`date_added`) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (!empty($data['filter_date_modified'])) {
			$sql .= " AND DATE(`date_modified`) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
		}

		if (!empty($data['filter_total'])) {
			$sql .= " AND `total` = '" . (float)$data['filter_total'] . "'";
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Orders By Store ID
	 *
	 * @param int $store_id primary key of the store record
	 *
	 * @return int
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $order_total = $this->model_sale_order->getTotalOrdersByStoreId($store_id);
	 */
	public function getTotalOrdersByStoreId(int $store_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order` WHERE `store_id` = '" . (int)$store_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Orders By Order Status ID
	 *
	 * @param int $order_status_id primary key of the order status record
	 *
	 * @return int total number of order records that have order status ID
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $order_total = $this->model_sale_order->getTotalOrdersByOrderStatusId($order_status_id);
	 */
	public function getTotalOrdersByOrderStatusId(int $order_status_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order` WHERE `order_status_id` = '" . (int)$order_status_id . "' AND `order_status_id` > '0'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Orders By Processing Status
	 *
	 * @return int total number of order processing status records
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $order_total = $this->model_sale_order->getTotalOrdersByProcessingStatus();
	 */
	public function getTotalOrdersByProcessingStatus(): int {
		$implode = [];

		$order_statuses = (array)$this->config->get('config_processing_status');

		foreach ($order_statuses as $order_status_id) {
			$implode[] = "`order_status_id` = '" . (int)$order_status_id . "'";
		}

		if ($implode) {
			$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order` WHERE " . implode(" OR ", $implode));

			return (int)$query->row['total'];
		} else {
			return 0;
		}
	}

	/**
	 * Get Total Orders By Complete Status
	 *
	 * @return int total number of order complete status records
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $order_total = $this->model_sale_order->getTotalOrdersByCompleteStatus();
	 */
	public function getTotalOrdersByCompleteStatus(): int {
		$implode = [];

		$order_statuses = (array)$this->config->get('config_complete_status');

		foreach ($order_statuses as $order_status_id) {
			$implode[] = "`order_status_id` = '" . (int)$order_status_id . "'";
		}

		if ($implode) {
			$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order` WHERE " . implode(" OR ", $implode) . "");

			return (int)$query->row['total'];
		} else {
			return 0;
		}
	}

	/**
	 * Get Total Orders By Language ID
	 *
	 * @param int $language_id primary key of the language record
	 *
	 * @return int total number of order records that have language ID
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $order_total = $this->model_sale_order->getTotalOrdersByLanguageId($language_id);
	 */
	public function getTotalOrdersByLanguageId(int $language_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order` WHERE `language_id` = '" . (int)$language_id . "' AND `order_status_id` > '0'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Orders By Currency ID
	 *
	 * @param int $currency_id primary key of the currency record
	 *
	 * @return int total number of order records that have currency ID
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $order_total = $this->model_sale_order->getTotalOrdersByCurrencyId($currency_id);
	 */
	public function getTotalOrdersByCurrencyId(int $currency_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order` WHERE `currency_id` = '" . (int)$currency_id . "' AND `order_status_id` > '0'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Sales
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return int total number of sale records
	 *
	 * @example
	 *
	 * $filter_data = [
	 *     'filter_order_id'        => 1,
	 *     'filter_customer_id'     => 1,
	 *     'filter_customer'        => 'John Doe',
	 *     'filter_store_id'        => 1,
	 *     'filter_order_status'    => 'Pending',
	 *     'filter_order_status_id' => 1,
	 *     'filter_total'           => 0.0000,
	 *     'filter_date_from'       => '2021-01-01',
	 *     'filter_date_to'         => '2021-01-31',
	 *     'sort'                   => 'o.order_id',
	 *     'order'                  => 'DESC',
	 *     'start'                  => 0,
	 *     'limit'                  => 10
	 * ];
	 *
	 * $this->load->model('sale/order');
	 *
	 * $sale_total = $this->model_sale_order->getTotalSales();
	 */
	public function getTotalSales(array $data = []): int {
		$sql = "SELECT SUM(`total`) AS `total` FROM `" . DB_PREFIX . "order`";

		if (!empty($data['filter_order_status'])) {
			$implode = [];

			$order_statuses = explode(',', $data['filter_order_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "`order_status_id` = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} elseif (isset($data['filter_order_status_id']) && $data['filter_order_status_id'] !== '') {
			$sql .= " WHERE `order_status_id` = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE `order_status_id` > '0'";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND `order_id` = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(`firstname`, ' ', `lastname`) LIKE '" . $this->db->escape('%' . $data['filter_customer'] . '%') . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(`date_added`) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (!empty($data['filter_date_modified'])) {
			$sql .= " AND DATE(`date_modified`) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
		}

		if (!empty($data['filter_total'])) {
			$sql .= " AND `total` = '" . (float)$data['filter_total'] . "'";
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	/**
	 * Create Invoice No
	 *
	 * @param int $order_id primary key of the order record
	 *
	 * @return string
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $invoice_no = $this->model_sale_order->createInvoiceNo($order_id);
	 */
	public function createInvoiceNo(int $order_id): string {
		$order_info = $this->getOrder($order_id);

		if ($order_info && !$order_info['invoice_no']) {
			$query = $this->db->query("SELECT MAX(`invoice_no`) AS `invoice_no` FROM `" . DB_PREFIX . "order` WHERE `invoice_prefix` = '" . $this->db->escape($order_info['invoice_prefix']) . "'");

			if ($query->row['invoice_no']) {
				$invoice_no = $query->row['invoice_no'] + 1;
			} else {
				$invoice_no = 1;
			}

			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET `invoice_no` = '" . (int)$invoice_no . "', `invoice_prefix` = '" . $this->db->escape($order_info['invoice_prefix']) . "' WHERE `order_id` = '" . (int)$order_id . "'");

			return $order_info['invoice_prefix'] . $invoice_no;
		}

		return '';
	}

	/**
	 * Get Histories
	 *
	 * @param int $order_id primary key of the order record
	 * @param int $start
	 * @param int $limit
	 *
	 * @return array<int, array<string, mixed>> history records that have order ID
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $results = $this->model_sale_order->getHistories($order_id, $start, $limit);
	 */
	public function getHistories(int $order_id, int $start = 0, int $limit = 10): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT `oh`.`date_added`, `os`.`name` AS `status`, `oh`.`comment`, `oh`.`notify` FROM `" . DB_PREFIX . "order_history` `oh` LEFT JOIN `" . DB_PREFIX . "order_status` `os` ON `oh`.`order_status_id` = `os`.`order_status_id` WHERE `oh`.`order_id` = '" . (int)$order_id . "' AND `os`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY `oh`.`date_added` DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	/**
	 * Get Total Histories
	 *
	 * @param int $order_id primary key of the order record
	 *
	 * @return int total number of history records that have order ID
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $history_total = $this->model_sale_order->getTotalHistories($order_id);
	 */
	public function getTotalHistories(int $order_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order_history` WHERE `order_id` = '" . (int)$order_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Histories By Order Status ID
	 *
	 * @param int $order_status_id primary key of the order status record
	 *
	 * @return int total number of history records that have order status ID
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $history_total = $this->model_sale_order->getTotalHistoriesByOrderStatusId($order_status_id);
	 */
	public function getTotalHistoriesByOrderStatusId(int $order_status_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order_history` WHERE `order_status_id` = '" . (int)$order_status_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Emails By Products Ordered
	 *
	 * @param array<int> $products array
	 * @param int        $start
	 * @param int        $end
	 *
	 * @return array<int, array<string, mixed>>
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $results = $this->model_sale_order->getEmailsByProductsOrdered($products, $start, $end);
	 */
	public function getEmailsByProductsOrdered(array $products, int $start, int $end): array {
		$implode = [];

		foreach ($products as $product_id) {
			$implode[] = "`op`.`product_id` = '" . (int)$product_id . "'";
		}

		$query = $this->db->query("SELECT DISTINCT `email` FROM `" . DB_PREFIX . "order` `o` LEFT JOIN `" . DB_PREFIX . "order_product` `op` ON (`o`.`order_id` = `op`.`order_id`) WHERE (" . implode(" OR ", $implode) . ") AND `o`.`order_status_id` <> '0' LIMIT " . (int)$start . "," . (int)$end);

		return $query->rows;
	}

	/**
	 * Get Total Emails By Products Ordered
	 *
	 * @param array<int> $products
	 *
	 * @return int
	 *
	 * @example
	 *
	 * $this->load->model('sale/order');
	 *
	 * $product_total = $this->model_sale_order->getTotalEmailsByProductsOrdered($products);
	 */
	public function getTotalEmailsByProductsOrdered(array $products): int {
		$implode = [];

		foreach ($products as $product_id) {
			$implode[] = "`op`.`product_id` = '" . (int)$product_id . "'";
		}

		$query = $this->db->query("SELECT COUNT(DISTINCT `email`) AS `total` FROM `" . DB_PREFIX . "order` `o` LEFT JOIN `" . DB_PREFIX . "order_product` `op` ON (`o`.`order_id` = `op`.`order_id`) WHERE (" . implode(" OR ", $implode) . ") AND `o`.`order_status_id` <> '0'");

		return (int)$query->row['total'];
	}
}
