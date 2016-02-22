<?php defined('SYSPATH') or die ('No direct script access.');

/**
 *
 *
 * @package	XM Cart
 * @category   Helpers
 * @author	 XM Media Inc.
 * @copyright  (c) 2016 XM Media Inc.
 */
class XM_Cart_Transfer {
	protected static $transfer_data = array();
	protected static $charge_data = array();

	public static function update_transfers_transactions() {
		Cart::load_stripe();

		$last_transfer_id = null;

		$last_strored_transfer = ORM::factory('Cart_Transfer')
			->order_by('date', 'DESC')
			->find();
		if ($last_strored_transfer->loaded()) {
			$last_transfer_id = $last_strored_transfer->transfer_id;
		}

		$i = 0;
		do {
			++ $i;

			set_time_limit(120);

			$transfers = Stripe_Transfer::all(array(
				'limit' => 100,
				'ending_before' => $last_transfer_id,
			));
			$transfer_count = count($transfers->data);

			foreach ($transfers->data as $transfer) {
				if ($transfer->transactions->has_more) {
					$requestor = new Stripe_ApiRequestor();

					list($response, $apiKey) = $requestor->request('get', $transfer->transactions->url, array('limit' => 100));
					$transaction_data_response = Stripe_Util::convertToStripeObject($response, $apiKey);
					$transaction_data = $transaction_data_response->data;
				} else {
					$transaction_data = $transfer->transactions->data;
				}

				// only store status="paid"
				if ($transfer->status == 'paid') {
					$existing_transfer = ORM::factory('Cart_Transfer')
						->where('transfer_id', '=', $transfer->id)
						->find();

					if ( ! $existing_transfer->loaded()) {
						$transfer_date = new DateTime('@'.$transfer->created);
						ORM::factory('Cart_Transfer')
							->values(array(
								'transfer_id' => $transfer->id,
								'date' => $transfer_date->format('Y-m-d'),
								'data' => $transfer->__toArray(),
							))
							->save();
					}
				}

				foreach ($transaction_data as $transaction) {
					self::$charge_data[$transaction->id] = array(
						'transfer_id' => $transfer->id,
						'fee' => $transaction->fee,
						'transfer_date' => $transfer->created,
					);

					// see above
					if ($transfer->status == 'paid') {
						$existing_transaction = ORM::factory('Cart_Transfer_Transaction')
							->where('transfer_id', '=', $transfer->id)
							->where('stripe_id', '=', $transaction->id)
							->find();

						if ( ! $existing_transaction->loaded()) {
							ORM::factory('Cart_Transfer_Transaction')
								->values(array(
									'transfer_id' => $transfer->id,
									'stripe_id' => $transaction->id,
									'data' => $transaction->__toArray(),
								))
								->save();
						}
					}
				}

				self::$transfer_data[$transfer->id] = $transfer->__toArray();
				$last_transfer_id = $transfer->id;
			}
		} while ($transfer_count == 100 && $i < 25);
	}

	public static function get_transaction($transaction_id) {
		if (isset(self::$charge_data[$transaction_id])) {
			self::$charge_data[$transaction_id];
		}

		$transaction = ORM::factory('Cart_Transfer_Transaction')
			->where('stripe_id', '=', $transaction_id)
			->find();
		if ( ! $transaction->loaded()) {
			return null;
		}

		$transfer = self::get_transfer($transaction->transfer_id);

		self::$charge_data[$transaction->stripe_id] = array(
			'transfer_id' => $transaction->transfer_id,
			'fee' => $transaction->data['fee'],
			'transfer_date' => $transfer['created'],
		);

		return self::$charge_data[$transaction_id];
	}

	public static function get_transfer($transfer_id) {
		if (isset(self::$transfer_data[$transfer_id])) {
			return self::$transfer_data[$transfer_id];
		}

		$transfer = ORM::factory('Cart_Transfer')
			->where('transfer_id', '=', $transfer_id)
			->find();
		if ( ! $transfer->loaded()) {
			return null;
		}

		self::$transfer_data[$transfer_id] = $transfer->data;

		return self::$transfer_data[$transfer_id];
	}

	public static function get_all_transfers() {
		return ORM::factory('Cart_Transfer')
			->order_by('date', 'DESC')
			->find_all();
	}
}