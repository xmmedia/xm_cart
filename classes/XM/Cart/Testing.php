<?php defined('SYSPATH') or die ('No direct script access.');

/**
 *
 *
 * @package    XM Cart
 * @category   Helpers
 * @author     XM Media Inc.
 * @copyright  (c) 2014 XM Media Inc.
 */
class XM_Cart_Testing {
	/**
	 * Generates the array for the select to quicken testing of different credit card numbers/statuses.
	 *
	 * @return  array
	 */
	public static function card_testing_options() {
		$card_testing_options = array();

		$random_future_date = rand(time(), time() + (Date::YEAR * 10));
		$random_past_date = rand(time() - Date::MONTH, time() - (Date::YEAR * 10));

		$test_data = array(
			'card_number' => '4242424242424242',
			'security_code' => Text::random('numeric', 3),
			'expiry_date_month' => date('n', $random_future_date),
			'expiry_date_year' => date('Y', $random_future_date),
		);
		$card_testing_options[json_encode($test_data)] = 'Visa: Valid & Successful';

		$test_data = array(
			'card_number' => '4012 8888 8888 1881',
			'security_code' => Text::random('numeric', 3),
			'expiry_date_month' => date('n', $random_future_date),
			'expiry_date_year' => date('Y', $random_future_date),
		);
		$card_testing_options[json_encode($test_data)] = 'Visa: Valid & Successful';

		$test_data = array(
			'card_number' => '5555555555554444',
			'security_code' => Text::random('numeric', 3),
			'expiry_date_month' => date('n', $random_future_date),
			'expiry_date_year' => date('Y', $random_future_date),
		);
		$card_testing_options[json_encode($test_data)] = 'MC: Valid & Successful';

		$test_data = array(
			'card_number' => '5105 1051 0510 5100',
			'security_code' => Text::random('numeric', 3),
			'expiry_date_month' => date('n', $random_future_date),
			'expiry_date_year' => date('Y', $random_future_date),
		);
		$card_testing_options[json_encode($test_data)] = 'MC: Valid & Successful';

		$test_data = array(
			'card_number' => '4242424242424242',
			'security_code' => Text::random('numeric', 3),
			'expiry_date_month' => date('n', $random_past_date),
			'expiry_date_year' => date('Y', $random_past_date),
		);
		$card_testing_options[json_encode($test_data)] = 'Visa: Past Expiry';

		$test_data = array(
			'card_number' => '5105 1051 0510 5100',
			'security_code' => Text::random('numeric', 3),
			'expiry_date_month' => date('n', $random_past_date),
			'expiry_date_year' => date('Y', $random_past_date),
		);
		$card_testing_options[json_encode($test_data)] = 'MC: Past Expiry';

		$test_data = array(
			'card_number' => '4242424242424242',
			'security_code' => Text::random('numeric', 3),
			'expiry_date_month' => '',
			'expiry_date_year' => '',
		);
		$card_testing_options[json_encode($test_data)] = 'Visa: No Expiry';

		$test_data = array(
			'card_number' => '5105 1051 0510 5100',
			'security_code' => Text::random('numeric', 3),
			'expiry_date_month' => '',
			'expiry_date_year' => '',
		);
		$card_testing_options[json_encode($test_data)] = 'MC: No Expiry';

		$test_data = array(
			'card_number' => '4242424242424242',
			'security_code' => '',
			'expiry_date_month' => date('n', $random_future_date),
			'expiry_date_year' => date('Y', $random_future_date),
		);
		$card_testing_options[json_encode($test_data)] = 'Visa: No Security Code';

		$test_data = array(
			'card_number' => '5105 1051 0510 5100',
			'security_code' => '',
			'expiry_date_month' => date('n', $random_future_date),
			'expiry_date_year' => date('Y', $random_future_date),
		);
		$card_testing_options[json_encode($test_data)] = 'MC: No Security Code';

		$test_data = array(
			'card_number' => '4000000000000010',
			'security_code' => Text::random('numeric', 3),
			'expiry_date_month' => date('n', $random_future_date),
			'expiry_date_year' => date('Y', $random_future_date),
		);
		$card_testing_options[json_encode($test_data)] = 'Fail Billing Address Line 1 & Postal/Zip Checks';

		$test_data = array(
			'card_number' => '4000000000000028',
			'security_code' => Text::random('numeric', 3),
			'expiry_date_month' => date('n', $random_future_date),
			'expiry_date_year' => date('Y', $random_future_date),
		);
		$card_testing_options[json_encode($test_data)] = 'Fail Billing Address Line 1 Check';

		$test_data = array(
			'card_number' => '4000000000000036',
			'security_code' => Text::random('numeric', 3),
			'expiry_date_month' => date('n', $random_future_date),
			'expiry_date_year' => date('Y', $random_future_date),
		);
		$card_testing_options[json_encode($test_data)] = 'Fail Billing Address Postal/Zip Check';

		$test_data = array(
			'card_number' => '4000000000000101',
			'security_code' => Text::random('numeric', 3),
			'expiry_date_month' => date('n', $random_future_date),
			'expiry_date_year' => date('Y', $random_future_date),
		);
		$card_testing_options[json_encode($test_data)] = 'Fail Security Code Check';

		$test_data = array(
			'card_number' => '4000000000000002',
			'security_code' => Text::random('numeric', 3),
			'expiry_date_month' => date('n', $random_future_date),
			'expiry_date_year' => date('Y', $random_future_date),
		);
		$card_testing_options[json_encode($test_data)] = 'Declined';

		$test_data = array(
			'card_number' => '4000000000000127',
			'security_code' => Text::random('numeric', 3),
			'expiry_date_month' => date('n', $random_future_date),
			'expiry_date_year' => date('Y', $random_future_date),
		);
		$card_testing_options[json_encode($test_data)] = 'Declined: Incorrect Security Code';

		$test_data = array(
			'card_number' => '4000000000000069',
			'security_code' => Text::random('numeric', 3),
			'expiry_date_month' => date('n', $random_future_date),
			'expiry_date_year' => date('Y', $random_future_date),
		);
		$card_testing_options[json_encode($test_data)] = 'Declined: Expired Card';

		$test_data = array(
			'card_number' => '4000000000000119',
			'security_code' => Text::random('numeric', 3),
			'expiry_date_month' => date('n', $random_future_date),
			'expiry_date_year' => date('Y', $random_future_date),
		);
		$card_testing_options[json_encode($test_data)] = 'Declined: Processing Error';

		return $card_testing_options;
	}
}