<?php defined('SYSPATH') or die ('No direct script access.');

class XM_Cart {
	/**
	 * Returns the formatted number, with currency symbole.
	 *
	 * @param   float  $num  The float to format for currency.
	 * @return  string
	 */
	public static function cf($num) {
		return '$' . Num::format($num, 2, TRUE);
	}

	public static function address_html($str) {
		return UTF8::str_ireplace(array(PHP_EOL, '  '), array('<br>' . PHP_EOL, '&nbsp;&nbsp;'), HTML::chars($str));
	}

	public static function num_decimals($num) {
		$num = trim($num, 0);

		if ((int) $num == (float) $num) {
			return 0;
		}

		return (strlen($num) - strrpos($num, '.') - 1);
	}

	public static function countries() {
		$countries = array();
		foreach (ORM::factory('country')->find_all() as $country) {
			$countries[] = array('id' => $country->id, 'name' => $country->name);
		}

		return $countries;
	}

	public static function calc_method($calculation_method, $amount, $total) {
		if ($calculation_method == '%') {
				return $total * ($amount / 100);
			} else if ($calculation_method == '$') {
				return $amount;
			}
	}

	public static function calc_method_display($calculation_method, $amount) {
		return ($calculation_method == '$' ? '$' : '') . $amount . ($calculation_method == '%' ? '%' : '');
	}

	public static function total_rows($order) {
		$total_rows = array();

		$shipping = $order->cart_order_shipping->find();
		if ($shipping->loaded()) {
			$total_rows[] = array(
				'name' => $shipping->display_name,
				'value' => $shipping->amount,
				'value_formatted' => Cart::cf($shipping->amount),
			);
		}

		$total_rows[] = array(
			'name' => 'Sub Total',
			'value' => $order->sub_total,
			'value_formatted' => Cart::cf($order->sub_total),
		);

		foreach ($order->cart_order_tax->find_all() as $tax) {
			$total_rows[] = array(
				'name' => $tax->display_name,
				'value' => $tax->amount,
				'value_formatted' => Cart::cf($tax->amount),
			);
		}

		$total_rows[] = array(
			'name' => 'Total',
			'value' => $order->grand_total,
			'value_formatted' => Cart::cf($order->grand_total),
			'is_grand_total' => TRUE,
		);

		return $total_rows;
	}
}