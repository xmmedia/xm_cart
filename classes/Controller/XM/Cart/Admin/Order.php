<?php defined('SYSPATH') or die ('No direct script access.');

/**
 *
 *
 * @package    XM Cart
 * @category   Controllers
 * @author     XM Media Inc.
 * @copyright  (c) 2014 XM Media Inc.
 */
class Controller_XM_Cart_Admin_Order extends Controller_Cart_Admin {
	public $page = 'cart_admin';

	public $secure_actions = array(
		'index' => 'cart/admin/order',
		'reset_order_filters' => 'cart/admin/order',
		'view' => 'cart/admin/order',
		'refund' => 'cart/admin/order',
	);

	/**
	 * The default order filters. Set in before().
	 *
	 * @var  array
	 */
	protected $default_order_filters = array();

	/**
	 * Stores the order model.
	 *
	 * @var  Model_Cart_Order
	 */
	protected $order;

	public function before() {
		parent::before();

		$this->page_title_append = 'Orders - ' . $this->page_title_append;

		if ($this->auto_render) {
			$this->add_script('cart_admin_order', 'xm_cart/js/admin/order.min.js');
		}

		$this->default_order_filters = array(
			'status' => implode(',', array(CART_ORDER_STATUS_PAID, CART_ORDER_STATUS_RECEIVED)),
			'time_frame' => NULL,
			'time_frame_start' => NULL,
			'time_frame_end' => NULL,
		);

		$cart_admin_session = (array) Session::instance()->path('xm_cart.cart_admin');
		$cart_admin_session = array_replace_recursive(array(
			'order_filters' => $this->default_order_filters,
		), $cart_admin_session);
		Session::instance()->set_path('xm_cart.cart_admin', $cart_admin_session);

		$this->order = ORM::factory('Cart_Order', (int) $this->request->param('id'));
	}

	public function action_index() {
		$order_filters = (array) Session::instance()->path('xm_cart.cart_admin.order_filters');

		$received_order_filters = (array) $this->request->query('order_filters');
		if ( ! empty($received_order_filters)) {
			$received_order_filters += $order_filters;
			$order_filters = $received_order_filters;
			Session::instance()->set_path('xm_cart.cart_admin.order_filters', $received_order_filters);
		}

		$order_filters_html = array();
		$order_statuses = array(
			implode(',', array(CART_ORDER_STATUS_PAID, CART_ORDER_STATUS_RECEIVED)) => 'Paid & Not Shipped',
			'' => 'All Orders',
			implode(',', array(CART_ORDER_STATUS_NEW, CART_ORDER_STATUS_SUBMITTED, CART_ORDER_STATUS_PAYMENT)) => 'New & Not Paid',
			CART_ORDER_STATUS_NEW       => 'New Order / Unpaid',
			CART_ORDER_STATUS_SUBMITTED => 'Submitted / Waiting for Payment',
			CART_ORDER_STATUS_PAYMENT   => 'Payment in Progress',
			CART_ORDER_STATUS_PAID      => 'Paid',
			CART_ORDER_STATUS_RECEIVED  => 'Received',
			CART_ORDER_STATUS_SHIPPED   => 'Shipped',
			CART_ORDER_STATUS_REFUNDED  => 'Refunded',
			CART_ORDER_STATUS_CANCELLED => 'Cancelled',
			CART_ORDER_STATUS_EMPTIED   => 'Emptied',
		);
		$order_filters_html['status'] = Form::select('order_filters[status]', $order_statuses, $order_filters['status']);

		$time_frame_options = $this->time_frame_options();
		if (empty($order_filters['time_frame'])) {
			$order_filters['time_frame'] = key($time_frame_options);
			$time_frame_parts = explode('-', $order_filters['time_frame']);
			$order_filters['time_frame_start'] = substr($time_frame_parts[0], 0, 4) . '-' . substr($time_frame_parts[0], 4, 2) . '-' . substr($time_frame_parts[0], 6, 2);
			$order_filters['time_frame_end'] = substr($time_frame_parts[1], 0, 4) . '-' . substr($time_frame_parts[1], 4, 2) . '-' . substr($time_frame_parts[1], 6, 2);
		}
		$order_filters_html['time_frame_select'] = Form::select('order_filters[time_frame]', $time_frame_options, $order_filters['time_frame'], array('class' => 'js_cart_order_time_frame'));
		$order_filters_html['time_frame_start'] = Form::date('order_filters[time_frame_start]', $order_filters['time_frame_start'], array('class' => 'js_cart_order_time_frame_start'));
		$order_filters_html['time_frame_end'] = Form::date('order_filters[time_frame_end]', $order_filters['time_frame_end'], array('class' => 'js_cart_order_time_frame_end'));

		$orders = $this->get_orders($order_filters);

		$order_list = array(
			(string) View::factory('cart_admin/order/list_headers'),
		);

		foreach ($orders as $order) {
			$order->set_mode('view');

			$last_log = $order->cart_order_log->find();

			$view_uri = Route::get('cart_admin_order')->uri(array('action' => 'view', 'id' => $order->pk()));

			$order_list[] = (string) View::factory('cart_admin/order/item')
				->bind('order', $order)
				->bind('last_log', $last_log)
				->bind('view_uri', $view_uri)
				->set('billing_shipping_diff', $order->billing_shipping_diff());
		}

		$uri = Route::get('cart_admin_order')->uri();
		$form_open = Form::open($uri, array('method' => 'GET', 'class' => 'cart_form js_cart_order_filter_form'));
		$export_uri = Route::get('cart_admin_order_export')->uri() . '?' . http_build_query(array('order_filters' => $order_filters));
		$reset_order_filters_uri = Route::get('cart_admin_order')->uri(array('action' => 'reset_order_filters')) . '?' . http_build_query(array('order_filters' => $this->default_order_filters));

		$this->template->page_title = 'Orders - ' . $this->page_title_append;
		$this->template->body_html = View::factory('cart_admin/order/index')
			->set('form_open', $form_open)
			->bind('order_filters_html', $order_filters_html)
			->bind('reset_order_filters_uri', $reset_order_filters_uri)
			->set('export_uri', $export_uri)
			->set('order_html', implode(PHP_EOL, $order_list));
	}

	/**
	 * Resets the order filters in the session and redirects the user back to the index action.
	 *
	 * @return  void
	 */
	public function action_reset_order_filters() {
		$order_filters = $this->default_order_filters + (array) $this->request->query('order_filters');
		Session::instance()->set_path('xm_cart.cart_admin.order_filters', $order_filters);
		$this->redirect($this->order_uri());
	}

	public function action_view() {
		if ( ! $this->order->loaded()) {
			Message::add('The order could not be found.', Message::$error);
			$this->redirect($this->order_uri());
		}

		$this->order->set_mode('view');
		$donation_cart = (Cart_Config::donation_cart() && $this->order->donation_cart_flag);

		$order_products = $this->order->cart_order_product->find_all();

		$payment_transaction = $this->order->payment();
		$paid_with = array(
			'type' => $payment_transaction->response['card']['type'],
			'last_4' => $payment_transaction->response['card']['last4'],
		);

		$cart_view = ($donation_cart ? 'cart/cart_donation' : 'cart/cart');
		$cart_html = View::factory($cart_view)
			->bind('order_product_array', $order_products)
			->set('total_rows', Cart::total_rows($this->order));

		$actions = array();
		if (in_array($this->order->status, array(CART_ORDER_STATUS_PAID, CART_ORDER_STATUS_RECEIVED, CART_ORDER_STATUS_SHIPPED))) {
			$actions['Refund'] = array(
				'class' => 'js_cart_order_refund',
				'title' => 'Refund the entire order or a partial amount',
			);
			$actions['Cancel Order'] = array(
				'class' => 'js_cart_order_cancel',
				'title' => 'Cancel and refund the balance of the order',
			);
		}

		$status_options = array('' => 'Change Status To...');
		$status_options = Arr::merge($status_options, $this->allowed_order_statuses());

		if (count($status_options) > 1) {
			$status_form_open = Form::open(Route::get('cart_admin_order')->uri(array('action' => 'status_change', 'id' => $this->order->pk())), array('class' => 'js_order_status_change_form'));
			$status_select = Form::select('order_status', $status_options, NULL, array('class' => 'js_order_status_change'));
		}

		$this->add_style('cart_public', 'xm_cart/css/public.css');

		$this->template->page_title = ( ! empty($this->order->order_num) ? $this->order->order_num : 'Order View') . ' - ' . $this->page_title_append;
		$this->template->body_html = View::factory('cart_admin/order/view')
			->bind('order', $this->order)
			->bind('cart_html', $cart_html)
			->bind('paid_with', $paid_with)
			->bind('status_form_open', $status_form_open)
			->bind('status_select', $status_select)
			->bind('actions', $actions);
	}

	/**
	 * Changes the order status to the user selected one.
	 *
	 * @return  void
	 */
	public function action_status_change() {
		if ( ! $this->order->loaded()) {
			Message::add('The order could not be found.', Message::$error);
			$this->redirect($this->order_uri());
		}

		$order_uri = Route::get('cart_admin_order')->uri(array('action' => 'view', 'id' => $this->order->pk()));

		$order_status = $this->request->post('order_status');

		$allowed_statuses = $this->allowed_order_statuses();
		if (empty($order_status) || ! isset($allowed_statuses[$order_status])) {
			Message::add('The order status selected is not valid.', Message::$error);
			$this->redirect($order_uri);
		}

		$this->order
			->set('status', $order_status)
			->save();

		$order_status_labels = (array) Cart_Config::load('order_status_labels');
		Message::add('Order status changed to ' . HTML::chars($order_status_labels[$order_status]) . '.', Message::$notice);

		$this->redirect($order_uri);
	}

	/**
	 * For refunding and cancelling orders.
	 *
	 * @return  void
	 */
	public function action_refund() {
		if ( ! $this->order->loaded()) {
			Message::add('The order could not be found.', Message::$error);
			$this->redirect($this->order_uri());
		}

		$order_uri = Route::get('cart_admin_order')->uri(array('action' => 'view', 'id' => $this->order->pk()));

		$refund_type = strtolower($this->request->post('refund_type'));
		$refund_amount = floatval($this->request->post('refund_amount'));
		$cancel_order = (bool) $this->request->post('cancel_order');
		$send_email = (bool) $this->request->post('send_email');

		if ( ! $cancel_order && ! in_array($refund_type, array('full', 'partial'))) {
			Message::add('Select the type of refund before continuing.', Message::$error);
			$this->redirect($order_uri);
		}

		if ($refund_type == 'partial') {
			if ($refund_amount <= 0) {
				Message::add('The refund must be greater than $0.00.', Message::$error);
				$this->redirect($order_uri);
			} else if ($refund_amount > $this->order->final_total()) {
				Message::add('The refund cannot be greater than the order total of ' . Cart::cf($this->order->final_total()) . '.', Message::$error);
				$this->redirect($order_uri);
			}
		// either full refund or cancel order
		} else {
			$refund_amount = $this->order->final_total();
		}

		$payment_transaction = $this->order->payment();
		if ( ! $payment_transaction->loaded()) {
			throw new Kohana_Exception('The order payment could not be retrieved');
		}

		// load and configure stripe
		$stripe_config = Cart::load_stripe();

		$stripe_data = array(
			'charge_id' => $payment_transaction->transaction_id,
			'amount' => $refund_amount,
		);

		$this->order->add_log('processing_refund', array(
				'stripe_data' => $stripe_data,
			));

		$refund_transaction = ORM::factory('Cart_Order_Transaction')
			->values(array(
				'cart_order_id' => $this->order->pk(),
				'date_attempted' => Date::formatted_time(),
				'user_id' => (Auth::instance()->logged_in() ? Auth::instance()->get_user()->pk() : 0),
				'ip_address' => Arr::get($_SERVER, 'REMOTE_ADDR'),
				'payment_processor' => (int) Cart_Config::load('payment_processor_ids.stripe'),
				'type' => CART_TRANSACTION_TYPE_REFUND,
				'status' => CART_TRANSACTION_STATUS_IN_PROGRESS,
				'amount' => $refund_amount,
				'data' => $stripe_data,
			))
			->save()
			->add_log(CART_TRANSACTION_STATUS_IN_PROGRESS, $stripe_data);

		try {
			$error_data = FALSE;

			$charge = Stripe_Charge::retrieve($payment_transaction->transaction_id);
			$charge->refund(array(
				'amount' => Cart::total_cents($refund_amount),
			));

			$refund_transaction->values(array(
					'date_completed' => Date::formatted_time(),
					'status' => CART_TRANSACTION_STATUS_SUCCESSFUL,
					'transaction_id', $charge->balance_transaction,
					'response' => $charge->__toArray(TRUE),
				))
				->save()
				->add_log(CART_TRANSACTION_STATUS_SUCCESSFUL, $charge->__toArray(TRUE));

			$all_refunds = $this->order->cart_order_transaction
				->where('type', '=', CART_TRANSACTION_TYPE_REFUND)
				->where('status', '=', CART_TRANSACTION_STATUS_SUCCESSFUL)
				->find_all();
			$refund_total = 0;
			foreach ($all_refunds as $_refund) {
				$refund_total += $_refund->amount;
			}

			$this->order->set('refund_total', $refund_total)
				->save()
				->add_log('processed_refund', array(
					'stripe_data' => $stripe_data,
				))
				->reload();

			if ($cancel_order) {
				$this->order->set('status', CART_ORDER_STATUS_CANCELLED)
					->save()
					->add_log('cancelled', array(
						'stripe_data' => $stripe_data,
					));
				Message::add('The order has been refunded &amp; cancelled.', Message::$notice);
				$email_msg = 'email.customer_order.cancelled';
			} else {
				if ($this->order->refund_total >= $this->order->grand_total) {
					$this->order->set('status', CART_ORDER_STATUS_REFUNDED)
						->save()
						->add_log('refunded', array(
							'stripe_data' => $stripe_data,
						));
					Message::add('The order has been fully refunded.', Message::$notice);
					$email_msg = 'email.customer_order.full_refund';
				} else {
					Message::add('The partial refund of ' . Cart::cf($refund_amount) . ' has been completed.', Message::$notice);
					$email_msg = '<p>' . HTML::chars(Cart::message('email.customer_order.partial_refund', array(':amount' => Cart::cf($refund_amount)))) . '</p>';
				}
			}

			if ($send_email) {
				Cart::send_customer_order_email($this->order, $payment_transaction, $email_msg);
			}
		} catch (Stripe_InvalidRequestError $e) {
			// Invalid parameters were supplied to Stripe's API
			Kohana::$log->add(Kohana_Log::ERROR, 'Invalid parameters were supplied to Stripe\'s API')->write();
			Kohana_Exception::log($e);
			$error_data = array(
				'type' => 'invalid_request',
			);

		} catch (Stripe_AuthenticationError $e) {
			// Authentication with Stripe's API failed
			// (maybe you changed API keys recently)
			Kohana::$log->add(Kohana_Log::ERROR, 'Authentication with Stripe\'s API failed')->write();
			Kohana_Exception::log($e);
			$error_data = array(
				'type' => 'authentication_error',
			);

		} catch (Stripe_ApiConnectionError $e) {
			// Network communication with Stripe failed
			Kohana::$log->add(Kohana_Log::ERROR, 'Network communication with Stripe failed')->write();
			Kohana_Exception::log($e);
			$error_data = array(
				'type' => 'api_connection_error',
			);

		} catch (Stripe_Error $e) {
			// Display a very generic error to the user
			Kohana::$log->add(Kohana_Log::ERROR, 'General Stripe error')->write();
			Kohana_Exception::log($e);
			$error_data = array(
				'type' => 'stripe_error',
			);

		} catch (Exception $e) {
			Kohana_Exception::log($e);
			Message::add(Cart::message('fail'), Message::$error);
			$error_data = TRUE;
		}

		if ($error_data) {
			$this->order->add_log('refund_error', $error_data);
			$refund_transaction->values(array(
					'status' => CART_TRANSACTION_STATUS_ERROR,
					'response' => $error_data,
				))
				->save()
				->add_log(CART_TRANSACTION_STATUS_ERROR, $error_data);

			Message::add(Cart::message('stripe.error_refund'), Message::$error);
		}

		$this->redirect($order_uri);
	}

	/**
	 * Creates the array of time frame options.
	 *
	 * @return  array
	 */
	protected function time_frame_options() {
		$time_frame_options = array();
		$time_frame_date_format = 'Ymd';
		$time_frame_date_separator = '-';
		$months_long = Date::months(Date::MONTHS_LONG);

		$sunday_str = date('w') == 0 ? 'now' : 'last Sunday';
		$saturday_str = date('w') == 6 ? 'now' : 'next Saturday';
		$time_frame_str = Date::formatted_time($sunday_str, $time_frame_date_format)
			. $time_frame_date_separator . Date::formatted_time($saturday_str, $time_frame_date_format);
		$time_frame_options[$time_frame_str] = 'This Week';

		$time_frame_str = Date::formatted_time('-2 Sunday', $time_frame_date_format)
			. $time_frame_date_separator . Date::formatted_time('last Saturday', $time_frame_date_format);
		$time_frame_options[$time_frame_str] = 'Last Week';

		$semimonthly_start_day = date('j') < 15 ? '01' : '15';
		$semimonthly_end_day = date('j') < 15 ? '15' : 't';
		$time_frame_str = Date::formatted_time('now', 'Ym' . $semimonthly_start_day)
			. $time_frame_date_separator . Date::formatted_time('now', 'Ym' . $semimonthly_end_day);
		$time_frame_options[$time_frame_str] = 'This Semimonthly Period';

		if (date('j') < 15) {
			$semimonthly_str = 'previous month';
			$semimonthly_start_day = '15';
			$semimonthly_end_day = 't';
		} else {
			$semimonthly_str = 'now';
			$semimonthly_start_day = '01';
			$semimonthly_end_day = '15';
		}
		$time_frame_str = Date::formatted_time($semimonthly_str, 'Ym' . $semimonthly_start_day)
			. $time_frame_date_separator . Date::formatted_time($semimonthly_str, 'Ym' . $semimonthly_end_day);
		$time_frame_options[$time_frame_str] = 'Last Semimonthly Period';

		$time_frame_str = Date::formatted_time('now', 'Ym01')
			. $time_frame_date_separator . Date::formatted_time('now', 'Ymt');
		$time_frame_options[$time_frame_str] = 'This Month (' . Date::formatted_time('now', 'F') . ')';

		$time_frame_str = Date::formatted_time('last month', 'Ym01')
			. $time_frame_date_separator . Date::formatted_time('last month', 'Ymt');
		$time_frame_options[$time_frame_str] = 'Last Month (' . Date::formatted_time('last month', 'F') . ')';

		$current_quarter = ceil(date('m') / 3);
		$quarter_start_month = ($current_quarter - 1) * 3 + 1;
		$quarter_end_month = $current_quarter * 3;
		$time_frame_str = Date::formatted_time($months_long[$quarter_start_month], 'Ym01')
			. $time_frame_date_separator . Date::formatted_time($months_long[$quarter_end_month], 'Ymt');
		$time_frame_options[$time_frame_str] = 'This Quarter';

		$last_quarter = $current_quarter - 1;
		if ($last_quarter < 1) {
			$last_quarter = 4;
		}
		$quarter_start_month = ($last_quarter - 1) * 3 + 1;
		$quarter_end_month = $last_quarter * 3;
		$quarter_year = $last_quarter == 4 ? Date::formatted_time('last year', 'Y') : 'Y';
		$time_frame_str = Date::formatted_time($months_long[$quarter_start_month], $quarter_year . 'm01')
			. $time_frame_date_separator . Date::formatted_time($months_long[$quarter_end_month], $quarter_year . 'mt');
		$time_frame_options[$time_frame_str] = 'Last Quarter';

		$time_frame_str = Date::formatted_time('January', 'Ym01')
			. $time_frame_date_separator . Date::formatted_time('December', 'Ymt');
		$time_frame_options[$time_frame_str] = 'This Year';

		$time_frame_str = Date::formatted_time('last year January', 'Ym01')
			. $time_frame_date_separator . Date::formatted_time('last year December', 'Ymt');
		$time_frame_options[$time_frame_str] = 'Last Year';

		$time_frame_options['custom'] = 'Custom';

		return $time_frame_options;
	}
}