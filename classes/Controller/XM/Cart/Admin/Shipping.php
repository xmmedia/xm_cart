<?php defined('SYSPATH') or die ('No direct script access.');

/**
 *
 *
 * @package    XM Cart
 * @category   Controllers
 * @author     XM Media Inc.
 * @copyright  (c) 2014 XM Media Inc.
 */
class Controller_XM_Cart_Admin_Shipping extends Controller_Cart_Admin {
	public $page = 'cart_admin';

	public $secure_actions = array(
		'index' => 'cart/admin/shipping',
		'edit' => 'cart/admin/shipping',
		'delete' => 'cart/admin/shipping',
	);

	public function before() {
		parent::before();

		$this->page_title_append = 'Shipping - ' . $this->page_title_append;

		if ($this->auto_render) {
			$this->add_script('cart_admin_shipping', 'xm_cart/js/admin/shipping.min.js');
		}
	}

	public function action_index() {
		if ( ! Cart_Config::enable_shipping()) {
			Message::add('Shipping is not enabled on your cart.', Message::$error);
			$this->redirect($this->order_uri());
		}

		$shipping_rates = ORM::factory('Cart_Shipping')
			->where_active_dates()
			->find_all();

		$shipping_rate_html = array();
		foreach ($shipping_rates as $shipping_rate) {
			$html = '<strong>' . HTML::chars($shipping_rate->name) . '</strong>'
				. ($shipping_rate->name != $shipping_rate->display_name ? ' (' . HTML::chars($shipping_rate->display_name) . ')' : '');
			if ( ! Form::check_date_empty_value($shipping_rate->start)) {
				$html .= '<br>Starting ' . $shipping_rate->start;
			}
			if ( ! Form::check_date_empty_value($shipping_rate->end)) {
				$html .= '<br>Ending ' . $shipping_rate->end;
			}

			if ( ! empty($shipping_rate->data['reasons']) && is_array($shipping_rate->data['reasons'])) {
				foreach ($shipping_rate->data['reasons'] as $reason) {
					switch ($reason['reason']) {
						case 'flat_rate' :
							$html .= '<br>Flat Rate';
							break;

						case 'sub_total' :
							$html .= '<br>Order Sub Total ';
							if (isset($reason['min']) && isset($reason['max'])) {
								$html .= 'between ' . Cart::cf($reason['min']) . ' and ' . Cart::cf($reason['max']);
							} else if (isset($reason['greater_than'])) {
								$html .= 'greater than ' . Cart::cf($reason['greater_than']);
							}
							break;

						case 'shipping_address' :
							$html .= '<br>Shipping Address in ';
							// both country & state(s)
							if (isset($reason['state_id'])) {
								$_country = ORM::factory('country', $reason['country_id']);
								if ($_country->loaded()) {
									$html .= HTML::chars($_country->name) . ': ';

									$_states = array();
									foreach ((array) $reason['state_id'] as $_state_id) {
										$_state = ORM::factory('State', $_state_id);
										if ($_state->loaded()) {
											$_states[] = $_state->name;
										}
									}
									$html .= implode(' or ', $_states);
								} else {
									$html .= 'Unknown';
								}

							// only country(ies)
							} else {
								$_countries = array();
								foreach ((array) $reason['country_id'] as $_country_id) {
									$_country = ORM::factory('Country', $_country_id);
									if ($_country->loaded()) {
										$_countries[] = $_country->name;
									}
								}
								$html .= implode(' or ', $_countries);
							}
							break;
					}
				}
			}

			$html .= '<br>' . Cart::calc_method_display($shipping_rate->calculation_method, $shipping_rate->amount);

			$html .= '<br>' . HTML::anchor(Route::get('cart_admin')->uri(array('action' => 'shipping_edit', 'id' => $shipping_rate->pk())), 'Edit') . ' | '
				. HTML::anchor(Route::get('cart_admin')->uri(array('action' => 'shipping_delete', 'id' => $shipping_rate->pk())), 'Delete', array('class' => 'js_delete_shipping', 'data-name' => $shipping_rate->name));;

			$shipping_rate_html[] = $html;
		}

		$add_uri = Route::get('cart_admin')->uri(array('action' => 'shipping_edit')) . '?add=1';

		$this->template->page_title = 'Shipping Rates - ' . $this->page_title_append;
		$this->template->body_html = View::factory('cart_admin/shipping/index')
			->bind('add_uri', $add_uri)
			->bind('shipping_rate_html', $shipping_rate_html);
	}

	public function action_edit() {
		if ( ! Cart_Config::enable_shipping()) {
			Message::add('Shipping is not enabled on your cart.', Message::$error);
			$this->redirect($this->order_uri());
		}

		$add = (bool) $this->request->query('add');
		if ($add) {
			$shipping_rate = ORM::factory('Cart_Shipping');
		} else {
			$shipping_rate = ORM::factory('Cart_Shipping', (int) $this->request->param('id'));
			if ( ! $shipping_rate->loaded()) {
				Message::add('The shipping rate could not be found.', Message::$error);
				$this->redirect($this->shipping_uri());
			}
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
			'sub_total' => 'Order Sub Total',
			'shipping_address' => 'Shipping Address',
		);

		$uri = Route::get('cart_admin')->uri(array('action' => 'shipping_edit', 'id' => $shipping_rate->pk())) . ($add ? '?add=1' : '');

		$this->template->page_title = 'Shipping Rate Edit - ' . $this->page_title_append;
		$this->template->body_html = View::factory('cart_admin/shipping/edit')
			->set('form_open', Form::open($uri, array('class' => 'cart_form')))
			->set('cancel_uri', URL::site($this->shipping_uri()))
			->bind('shipping_rate', $shipping_rate)
			->bind('reasons', $reasons);
	}

	public function action_delete() {
		if ( ! Cart_Config::enable_shipping()) {
			Message::add('Shipping is not enabled on your cart.', Message::$error);
			$this->redirect($this->order_uri());
		}

		$shipping_rate = ORM::factory('Cart_Shipping', (int) $this->request->param('id'));
		if ( ! $shipping_rate->loaded()) {
			Message::add('The shipping rate could not be found.', Message::$error);
			$this->redirect($this->shipping_uri());
		}

		$shipping_rate->delete();

		Message::add('The shipping rate has been deleted.', Message::$notice);
		$this->redirect($this->shipping_uri());
	}

	protected function uri() {
		return Route::get('cart_admin')->uri(array('action' => 'shipping'));
	}
}