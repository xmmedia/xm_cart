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
}