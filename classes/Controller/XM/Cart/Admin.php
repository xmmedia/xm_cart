<?php defined('SYSPATH') or die ('No direct script access.');

class Controller_XM_Cart_Admin extends Controller_Private {
	public $page = 'cl4admin';

	public function before() {
		parent::before();

		if ($this->auto_render) {
			$this->add_style('cart_private', 'xm_cart/css/private.css');
		}
	}

	/**
	 * List recent orders, show current shipping and tax rates, available discounts.
	 */
	public function action_index() {
		$this->template->page_title = 'Cart Admin' . $this->page_title_append;
		$this->template->body_html = View::factory('cart_admin/index');
	}

	public function action_shipping() {
		$shipping_rates = ORM::factory('Cart_Shipping')
			->where_active_dates()
			->find_all();

		$shipping_rate_html = array();
		foreach ($shipping_rates as $shipping_rate) {
			$html = '<strong>' . HTML::chars($shipping_rate->name) . '</strong>'
				. ($shipping_rate->name != $shipping_rate->display_name ? ' (' . HTML::chars($shipping_rate->display_name) . ')' : '')
				. '<br>';
			if ( ! Form::check_date_empty_value($shipping_rate->start)) {
				$html .= 'Starting ' . $shipping_rate->start;
			}
			if ( ! Form::check_date_empty_value($shipping_rate->end)) {
				$html .= ' Ending ' . $shipping_rate->end;
			}

			if ( ! empty($shipping_rate->data['reasons']) && is_array($shipping_rate->data['reasons'])) {
				foreach ($shipping_rate->data['reasons'] as $reason) {
					switch ($reason['reason']) {
						case 'flat_rate' :
							$html .= '<br>Flat Rate';
							break;
					} // switch reasons
				} // foreach reasons
			}

			$html .= '<br>' . Cart::calc_method_display($shipping_rate->calculation_method, $shipping_rate->amount);

			$html .= '<br>' . HTML::anchor(Route::get('cart_admin')->uri(array('action' => 'shipping_edit', 'id' => $shipping_rate->pk())), 'Edit') . ' | '
				. HTML::anchor(Route::get('cart_admin')->uri(array('action' => 'shipping_delete', 'id' => $shipping_rate->pk())), 'Delete');

			$shipping_rate_html[] = $html;
		}

		$this->template->page_title = 'Shipping Rates - Cart Admin' . $this->page_title_append;
		$this->template->body_html = View::factory('cart_admin/shipping')
			->bind('shipping_rate_html', $shipping_rate_html);
	}

	public function action_shipping_edit() {
		$shipping_rate_id = $this->request->param('id');
		$shipping_rate = ORM::factory('Cart_Shipping', $shipping_rate_id);
		if ( ! $shipping_rate->loaded()) {
			Message::add('The shipping rate could not be found.', Message::$error);
			$this->redirect($this->shipping_uri());
		}

		if ( ! empty($_POST)) {
			try {
				$shipping_rate->save_values()
					->set('data', array('reasons' => (array) $this->request->post('reasons')))
					->save();

				Message::add('The shipping rate has been saved.', Message::$notice);
				$this->redirect($this->shipping_uri());

			} catch (ORM_Validation_Exception $e) {
				Message::add('Please fix the following errors: ' . Message::add_validation_errors($e, ''), Message::$error);
			}
		}

		$reasons = array(
			'flat_rate' => 'Flat Rate',
		);

		$uri = Route::get('cart_admin')->uri(array('action' => 'shipping_edit', 'id' => $shipping_rate->pk()));
		$cancel_uri = URL::site($this->shipping_uri());

		$this->template->page_title = 'Shipping Rate Edit - Cart Admin' . $this->page_title_append;
		$this->template->body_html = View::factory('cart_admin/shipping_edit')
			->set('form_open', Form::open($uri, array('class' => 'cart_form')))
			->bind('cancel_uri', $cancel_uri)
			->bind('shipping_rate', $shipping_rate)
			->bind('reasons', $reasons);
	}

	public function shipping_uri() {
		return Route::get('cart_admin')->uri(array('action' => 'shipping'));
	}
}