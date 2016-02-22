<?php defined('SYSPATH') or die ('No direct script access.');

/**
 *
 *
 * @package    XM Cart
 * @category   Controllers
 * @author     XM Media Inc.
 * @copyright  (c) 2014 XM Media Inc.
 */
class Controller_XM_Cart_Admin extends Controller_Private {
	public $page = 'cart_admin';

	public function before() {
		parent::before();

		$this->page_title_append = 'Cart Admin - ' . $this->page_title_append;

		if ($this->auto_render) {
			$this->add_style('cart_private', 'xm_cart/css/private.css')
				->add_script('cart_base', 'xm_cart/js/base.min.js');
		}
	}

	/**
	 * List recent orders, show current shipping and tax rates, available discounts.
	 */
	public function action_index() {
		$this->template->page_title = $this->page_title_append;
		$this->template->body_html = View::factory('cart_admin/index');
	}

	protected function order_uri() {
		return Route::get('cart_admin_order')->uri();
	}

	protected function shipping_uri() {
		return Route::get('cart_admin_shipping')->uri();
	}

	protected function tax_uri() {
		return Route::get('cart_admin_tax')->uri();
	}

	/**
	 * Returns the list of orders filter by the order filters passed in.
	 *
	 * @param   array  $order_filters  Array of order filters.
	 *
	 * @return  Database_Result
	 */
	protected function get_orders($order_filters) {
		$order_query = ORM::factory('Cart_Order');

		if ( ! empty($order_filters['status'])) {
			$order_filter_statuses = explode(',', $order_filters['status']);
			$order_query->where('cart_order.status', 'IN', $order_filter_statuses);
		}

		if ( ! empty($order_filters['time_frame_start']) && ! empty($order_filters['time_frame_end'])) {
			$last_log_select = DB::select(array(DB::expr('MAX(id)'), 'max_id'), 'cart_order_id')
				->from('cart_order_log')
				->group_by('cart_order_id');

			$order_query->join(array($last_log_select, 'last_log'))
					->on('last_log.cart_order_id', '=', 'cart_order.id')
				->join(array('cart_order_log', 'log'))
					->on('log.id', '=', 'last_log.max_id')
				->where_open()
					->where('log.timestamp', '>=', $order_filters['time_frame_start'] . ' 00:00:00')
					->where('log.timestamp', '<=', $order_filters['time_frame_end'] . ' 23:59:59')
				->where_close();
		}

		return $order_query->find_all();
	}

	/**
	 * Return a list of statuses that the order can be changed to.
	 *
	 * @return  array
	 */
	protected function allowed_order_statuses() {
		$status_options = array();

		// don't allow status changes for any orders that have not been completed yet
		$completed_statuses = array(
			CART_ORDER_STATUS_PAID,
			CART_ORDER_STATUS_RECEIVED,
			CART_ORDER_STATUS_SHIPPED,
			CART_ORDER_STATUS_REFUNDED,
			CART_ORDER_STATUS_CANCELLED,
		);
		if (in_array($this->order->status, $completed_statuses)) {
			$order_status_labels = (array) Cart_Config::load('order_status_labels');

			if ($this->order->final_total() > 0) {
				$status_options[CART_ORDER_STATUS_PAID] = $order_status_labels[CART_ORDER_STATUS_PAID];
				$status_options[CART_ORDER_STATUS_RECEIVED] = $order_status_labels[CART_ORDER_STATUS_RECEIVED];
				if (Cart_Config::enable_shipping()) {
					$status_options[CART_ORDER_STATUS_SHIPPED] = $order_status_labels[CART_ORDER_STATUS_SHIPPED];
				}
				$status_options[CART_ORDER_STATUS_REFUNDED] = $order_status_labels[CART_ORDER_STATUS_REFUNDED];
			}
			$status_options[CART_ORDER_STATUS_CANCELLED] = $order_status_labels[CART_ORDER_STATUS_CANCELLED];

			if (isset($status_options[$this->order->status])) {
				unset($status_options[$this->order->status]);
			}
		}

		return $status_options;
	}
}