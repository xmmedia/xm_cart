<?php defined('SYSPATH') or die ('No direct script access.');

/**
 *
 *
 * @package    XM Cart
 * @category   Controllers
 * @author     XM Media Inc.
 * @copyright  (c) 2014 XM Media Inc.
 */
class Controller_XM_Cart_Admin_Tax extends Controller_Cart_Admin {
	public $page = 'cart_admin';

	public $secure_actions = array(
		'index' => 'cart/admin/tax',
		'edit' => 'cart/admin/tax',
		'delete' => 'cart/admin/tax',
	);

	public function before() {
		parent::before();

		$this->page_title_append = 'Taxes - ' . $this->page_title_append;

		if ($this->auto_render) {
			$this->add_script('cart_admin_tax', 'xm_cart/js/admin/tax.min.js');
		}

		$cart_admin_session = (array) Session::instance()->path('xm_cart.cart_admin');
		$cart_admin_session += array(
			'order_filters' => array(
				'status' => implode(',', array(CART_ORDER_STATUS_PAID, CART_ORDER_STATUS_RECEIVED)),
			),
		);
		Session::instance()->set_path('xm_cart.cart_admin', $cart_admin_session);
	}

	public function action_index() {
		if ( ! Cart_Config::enable_tax()) {
			Message::add('Taxes are not enabled on your cart.', Message::$error);
			$this->redirect($this->order_uri());
		}

		$taxes = ORM::factory('Cart_Tax')
			->where_active_dates()
			->find_all();

		$taxes_html = array();
		foreach ($taxes as $tax) {
			$html = '<strong>' . HTML::chars($tax->name) . '</strong>'
				. ($tax->name != $tax->display_name ? ' (' . HTML::chars($tax->display_name) . ')' : '');
			if ( ! Form::check_date_empty_value($tax->start)) {
				$html .= '<br>Starting ' . $tax->start;
			}
			if ( ! Form::check_date_empty_value($tax->end)) {
				$html .= '<br>Ending ' . $tax->end;
			}

			$html .= '<br>' . Cart::calc_method_display($tax->calculation_method, $tax->amount);

			$html .= '<br>' . HTML::anchor(Route::get('cart_admin_tax')->uri(array('action' => 'edit', 'id' => $tax->pk())), 'Edit') . ' | '
				. HTML::anchor(Route::get('cart_admin_tax')->uri(array('action' => 'delete', 'id' => $tax->pk())), 'Delete', array('class' => 'js_delete_tax', 'data-name' => $tax->name));

			$taxes_html[] = $html;
		}

		$add_uri = Route::get('cart_admin_tax')->uri(array('action' => 'edit')) . '?add=1';

		$this->template->page_title = 'Taxes - ' . $this->page_title_append;
		$this->template->body_html = View::factory('cart_admin/tax/index')
			->bind('add_uri', $add_uri)
			->bind('taxes_html', $taxes_html);
	}

	public function action_edit() {
		if ( ! Cart_Config::enable_tax()) {
			Message::add('Taxes are not enabled on your cart.', Message::$error);
			$this->redirect($this->order_uri());
		}

		$add = (bool) $this->request->query('add');
		if ($add) {
			$tax = ORM::factory('Cart_Tax')
				->set_mode('add');
		} else {
			$tax = ORM::factory('Cart_Tax', (int) $this->request->param('id'));
			if ( ! $tax->loaded()) {
				Message::add('The tax could not be found.', Message::$error);
				$this->redirect($this->tax_uri());
			}
		}

		if ( ! empty($_POST)) {
			try {
				$tax->save_values()
					->save();

				Message::add('The tax has been saved.', Message::$notice);
				$this->redirect($this->tax_uri());

			} catch (ORM_Validation_Exception $e) {
				Message::add('Please fix the following errors: ' . Message::add_validation_errors($e, ''), Message::$error);
			}
		}

		$uri = Route::get('cart_admin_tax')->uri(array('action' => 'edit', 'id' => $tax->pk())) . ($add ? '?add=1' : '');

		$this->template->page_title = 'Tax Edit - ' . $this->page_title_append;
		$this->template->body_html = View::factory('cart_admin/tax/edit')
			->set('form_open', Form::open($uri, array('class' => 'cart_form')))
			->set('cancel_uri', URL::site($this->tax_uri()))
			->bind('tax', $tax);
	}

	public function action_delete() {
		if ( ! Cart_Config::enable_tax()) {
			Message::add('Taxes are not enabled on your cart.', Message::$error);
			$this->redirect($this->order_uri());
		}

		$tax = ORM::factory('Cart_Tax', (int) $this->request->param('id'));
		if ( ! $tax->loaded()) {
			Message::add('The tax could not be found.', Message::$error);
			$this->redirect($this->tax_uri());
		}

		$tax->delete();

		Message::add('The tax has been deleted.', Message::$notice);
		$this->redirect($this->tax_uri());
	}
}