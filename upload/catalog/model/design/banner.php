<?php
/**
 * Class Banner
 *
 * Can be called using $this->load->model('design/banner');
 *
 * @package Catalog\Model\Design
 */
class ModelDesignBanner extends Model {
	/**
	 * Get Banner
	 *
	 * @param int $banner_id primary key of the banner record
	 *
	 * @return array<int, array<string, mixed>> banner record that has banner ID
	 *
	 * @example
	 *
	 * $this->load->model('design/banner');
	 *
	 * $banner_info = $this->model_design_banner->getBanner($banner_id);
	 */
	public function getBanner(int $banner_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "banner` `b` LEFT JOIN `" . DB_PREFIX . "banner_image` `bi` ON (`b`.`banner_id` = `bi`.`banner_id`) WHERE `b`.`banner_id` = '" . (int)$banner_id . "' AND `b`.`status` = '1' AND `bi`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY `bi`.`sort_order` ASC");

		return $query->rows;
	}
}
