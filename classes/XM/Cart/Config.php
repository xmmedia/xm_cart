<?php defined('SYSPATH') or die ('No direct script access.');

class XM_Cart_Config {
	public static $continue_shopping_url;
	public static $enable_shipping;
	public static $enable_tax;
	public static $donation_cart;

	public static function continue_shopping_url() {
		if (Cart_Config::$continue_shopping_url === NULL) {
			Cart_Config::$continue_shopping_url = (string) Kohana::$config->load('xm_cart.continue_shopping_url');
		}

		return Cart_Config::$continue_shopping_url;
	}

	public static function enable_shipping() {
		if (Cart_Config::$enable_shipping === NULL) {
			Cart_Config::$enable_shipping = (bool) Kohana::$config->load('xm_cart.enable_shipping');
		}

		return Cart_Config::$enable_shipping;
	}

	public static function enable_tax() {
		if (Cart_Config::$enable_tax === NULL) {
			Cart_Config::$enable_tax = (bool) Kohana::$config->load('xm_cart.enable_tax');
		}

		return Cart_Config::$enable_tax;
	}

	public static function donation_cart() {
		if (Cart_Config::$donation_cart === NULL) {
			Cart_Config::$donation_cart = (bool) Kohana::$config->load('xm_cart.donation_cart');
		}

		return Cart_Config::$donation_cart;
	}
}