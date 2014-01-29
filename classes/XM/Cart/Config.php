<?php defined('SYSPATH') or die ('No direct script access.');

class XM_Cart_Config {
	public static $continue_shopping_url;
	public static $enable_shipping;
	public static $enable_tax;
	public static $donation_cart;

	public static function load($path) {
		return Kohana::$config->load('xm_cart.' . $path);
	}

	public static function continue_shopping_url() {
		if (Cart_Config::$continue_shopping_url === NULL) {
			Cart_Config::$continue_shopping_url = (string) Cart_Config::load('continue_shopping_url');
		}

		return Cart_Config::$continue_shopping_url;
	}

	public static function enable_shipping() {
		if (Cart_Config::$enable_shipping === NULL) {
			Cart_Config::$enable_shipping = (bool) Cart_Config::load('enable_shipping');
		}

		return Cart_Config::$enable_shipping;
	}

	public static function enable_tax() {
		if (Cart_Config::$enable_tax === NULL) {
			Cart_Config::$enable_tax = (bool) Cart_Config::load('enable_tax');
		}

		return Cart_Config::$enable_tax;
	}

	/**
	 * Checks if donations are enabled.
	 * Either the full and only donation cart setting or the donation cart and product cart can be enabled for this to return TRUE.
	 *
	 * @return  boolean
	 */
	public static function donation_cart() {
		if (Cart_Config::$donation_cart === NULL) {
			Cart_Config::$donation_cart = (bool) Cart_Config::load('donation_cart') || (bool) Cart_Config::load('donation_cart_or_product_cart');
		}

		return Cart_Config::$donation_cart;
	}
}