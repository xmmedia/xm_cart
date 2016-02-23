<?php defined('SYSPATH') or die ('No direct script access.');

/**
 * For exporting order data.
 *
 * @package    XM Cart
 * @category   Controllers
 * @author     XM Media Inc.
 * @copyright  (c) 2016 XM Media Inc.
 */
class Controller_XM_Cart_Admin_Order_Export extends Controller_Cart_Admin {
	public $page = 'cart_admin';

	public $secure_actions = array(
		'export' => 'cart/admin/order',
	);

	public function action_export() {
		Kohana::load(Kohana::find_file('vendor', 'phpexcel/PHPExcel'));
		$xlsx = new PHPExcel();

		Cart_Transfer::update_transfers_transactions();

		// ******************* Orders *********************
		$order_filters = (array) $this->request->query('order_filters');
		$orders = $this->get_orders($order_filters);

		$orderSheet = $xlsx->getActiveSheet();
		$this->setSheetTitle($orderSheet, 'Orders', 'B');
		$row_num = 1;

		$headings = array();
		$headings[] = array('name' => 'Order Number', 'width' => 12);
		$headings[] = array('name' => 'Status', 'width' => 10);
		$headings[] = array('name' => 'Date Paid', 'width' => 18);
		if (Cart_Config::enable_shipping()) {
			$headings[] = array('name' => 'Shipping Name', 'width' => 18);
			$headings[] = array('name' => 'Shipping Phone', 'width' => 13);
			$headings[] = array('name' => 'Shipping Email', 'width' => 25);
			$headings[] = array('name' => 'Shipping Address 1', 'width' => 25);
			$headings[] = array('name' => 'Shipping Address 2', 'width' => 18);
			$headings[] = array('name' => 'Shipping City', 'width' => 16);
			$headings[] = array('name' => 'Shipping Province/State', 'width' => 18);
			$headings[] = array('name' => 'Shipping Postal/Zip Code', 'width' => 18);
		}
		$headings[] = array('name' => 'Billing Name', 'width' => 18);
		$headings[] = array('name' => 'Billing Phone', 'width' => 13);
		$headings[] = array('name' => 'Billing Email', 'width' => 25);
		$headings[] = array('name' => 'Billing Address 1', 'width' => 25);
		$headings[] = array('name' => 'Billing Address 2', 'width' => 18);
		$headings[] = array('name' => 'Billing City', 'width' => 16);
		$headings[] = array('name' => 'Billing Province/State', 'width' => 18);
		$headings[] = array('name' => 'Billing Postal/Zip Code', 'width' => 18);
		$headings[] = array('name' => 'Paid With', 'width' => 15);
		$headings[] = array('name' => 'Stripe Charge ID', 'width' => 27);
		$headings[] = array('name' => 'Total', 'width' => 9);
		$headings[] = array('name' => 'Refund Total', 'width' => 11);
		$headings[] = array('name' => 'After Refunds', 'width' => 12);
		$headings[] = array('name' => 'Stripe Fee', 'width' => 9);
		$headings[] = array('name' => 'After Fee', 'width' => 9);
		$headings[] = array('name' => 'Transfer', 'width' => 27);
		$headings[] = array('name' => 'Transfer Date', 'width' => 12);
		$headings[] = array('name' => 'Notes', 'width' => 100);

		$row_num += 2;
		XLS::add_headings($orderSheet, $headings, $row_num);
		$orderSheet->freezePane('A' . ($row_num + 1));

		$col = Cart_Config::enable_shipping() ? 'Y' : 'Q';
		$orderSheet->setCellValueExplicit($col . '2', 'Italic values are approximate calculations because the transfer has not been initiated in Stripe.');
		$orderSheet->getStyle($col . '2')->getFont()->setItalic(TRUE);

		if (Cart_Config::enable_shipping()) {
			$orderSheetCols = array(
				'total' => 'V',
				'refund' => 'W',
				'after_refunds' => 'X',
				'stripe_fee' => 'Y',
				'after_fee' => 'Z',
				'notes' => 'AC',
			);
		} else {
			$orderSheetCols = array(
				'total' => 'N',
				'refund' => 'O',
				'after_refunds' => 'P',
				'stripe_fee' => 'Q',
				'after_fee' => 'R',
				'notes' => 'U',
			);
		}

		$stripe_fee = '0.029';
		$stripe_flat_fee = '0.30';

		// order ids are used later
		$orderIds = array();
		foreach ($orders as $order) {
			++ $row_num;

			$orderIds[] = $order->id;

			$order->set_mode('view');

			$paid_log = $order->cart_order_log
				->where('cart_order_log.action', '=', 'paid')
				->find();
			$payment_transaction = $order->payment();
			$charge_id = $payment_transaction->transaction_id;

			$row_data = array(
				$order->order_num,
				$order->get_field('status'),
				$paid_log->timestamp,
			);

			if (Cart_Config::enable_shipping()) {
				$row_data[] = $order->shipping_first_name . ' ' . $order->shipping_last_name;
				$row_data[] = XM::format_phone($order->shipping_phone);
				$row_data[] = $order->shipping_email;
				$row_data[] = $order->shipping_address_1;
				$row_data[] = $order->shipping_address_2;
				$row_data[] = $order->shipping_municipality;
				$row_data[] = $order->shipping_state_select->name;
				$row_data[] = $order->shipping_postal_code;
			}

			$row_data[] = $order->billing_first_name . ' ' . $order->billing_last_name;
			$row_data[] = XM::format_phone($order->billing_phone);
			$row_data[] = $order->billing_email;
			$row_data[] = $order->billing_address_1;
			$row_data[] = $order->billing_address_2;
			$row_data[] = $order->billing_municipality;
			$row_data[] = $order->billing_state_select->name;
			$row_data[] = $order->billing_postal_code;

			$row_data[] = $payment_transaction->response['card']['type'] . ' ' . $payment_transaction->response['card']['last4'];
			$row_data[] = $charge_id;
			$row_data[] = $order->grand_total;
			$row_data[] = $order->refund_total;
			$row_data[] = $order->final_total();

			$charge_data = Cart_Transfer::get_transaction($charge_id);

			if ( ! empty($charge_data)) {
				$row_data[] = '='.($charge_data['fee'] / 100).'-('.$orderSheetCols['refund'].$row_num.'*'.$stripe_fee.')';
				// same formula below
				$row_data[] = '='.$orderSheetCols['after_refunds'].$row_num.'-'.$orderSheetCols['stripe_fee'].$row_num;
				$row_data[] = $charge_data['transfer_id'];
				$transfer_datetime = (new DateTime('@'.$charge_data['transfer_date']));
				$row_data[] = $transfer_datetime->format('Y-m-d');
			} else {
				// put in approximate calculations
				// similar formula above
				$row_data[] = '=('.$orderSheetCols['total'].$row_num.'*'.$stripe_fee.')+'.$stripe_flat_fee.'-('.$orderSheetCols['refund'].$row_num.'*'.$stripe_fee.')';
				// same formula above
				$row_data[] = '='.$orderSheetCols['after_refunds'].$row_num.'-'.$orderSheetCols['stripe_fee'].$row_num;
				$row_data[] = '';
				$row_data[] = '';

				$orderSheet->getStyle($orderSheetCols['stripe_fee'] . $row_num . ':' . $orderSheetCols['after_fee'] . $row_num)->getFont()->setItalic(TRUE);
			}

			$row_data[] = $order->order_note;

			XLS::add_row($orderSheet, $row_num, $row_data);
		}

		// formatting for the total col
		$orderSheet->getStyle($orderSheetCols['total'] . '4:' . $orderSheetCols['after_fee'] . $row_num)
			->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		// wrap the notes col
		$orderSheet->getStyle($orderSheetCols['notes'] . '4:' . $orderSheetCols['notes'] . $row_num)
			->getAlignment()->setWrapText(true);

		// ******************* Transfers *********************
		$transferSheet = $xlsx->createSheet();
		$this->setSheetTitle($transferSheet, 'Transfers to Bank', 'F');
		$row_num = 1;

		$headings = array();
		$headings[] = array('name' => 'Transfer ID', 'width' => 27);
		$headings[] = array('name' => 'Date', 'width' => 10);
		$headings[] = array('name' => 'Amount', 'width' => 10);
		$headings[] = array('name' => 'Status', 'width' => 8);

		$row_num += 2;
		XLS::add_headings($transferSheet, $headings, $row_num);
		$transferSheet->freezePane('A' . ($row_num + 1));

		$all_transfers = Cart_Transfer::get_all_transfers();

		foreach ($all_transfers as $transfer) {
			++ $row_num;

			$transfer_data = $transfer->data;

			$datetime = (new DateTime('@'.$transfer_data['created']));

			$row_data = array(
				$transfer->transfer_id,
				$datetime->format('Y-m-d'),
				$transfer_data['amount'] / 100,
				ucwords(str_replace('_', ' ', $transfer_data['status'])),
			);

			XLS::add_row($transferSheet, $row_num, $row_data);
		}

		// formatting for the amount col
		$transferSheet->getStyle('C4:C' . $row_num)
			->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

		// ******************* Donations *********************
		if (Cart_Config::donation_cart()) {
			$donation_product_id = Cart_Config::load('donation_product_id');

			$donationSheet = $xlsx->createSheet();
			$this->setSheetTitle($donationSheet, 'Donations', 'F');
			$row_num = 1;

			$headings = array();
			$headings[] = array('name' => 'Order Number', 'width' => 12);
			$headings[] = array('name' => 'Status', 'width' => 10);
			$headings[] = array('name' => 'Date Paid', 'width' => 18);
			$headings[] = array('name' => 'Amount', 'width' => 9);
			$headings[] = array('name' => 'Paid With', 'width' => 15);
			$headings[] = array('name' => 'Billing Name', 'width' => 18);
			$headings[] = array('name' => 'Billing Phone', 'width' => 13);
			$headings[] = array('name' => 'Billing Email', 'width' => 25);
			$headings[] = array('name' => 'Billing Address 1', 'width' => 25);
			$headings[] = array('name' => 'Billing Address 2', 'width' => 18);
			$headings[] = array('name' => 'Billing City', 'width' => 16);
			$headings[] = array('name' => 'Billing Province/State', 'width' => 18);
			$headings[] = array('name' => 'Billing Postal/Zip Code', 'width' => 18);
			$headings[] = array('name' => 'Transfer', 'width' => 27);
			$headings[] = array('name' => 'Transfer Date', 'width' => 12);
			$headings[] = array('name' => 'Notes', 'width' => 100);

			$row_num += 2;
			XLS::add_headings($donationSheet, $headings, $row_num);
			$donationSheet->freezePane('A' . ($row_num + 1));

			$orderProducts = ORM::factory('Cart_Order_Product')
				->where('cart_order_product.cart_product_id', '=', $donation_product_id)
				->join('cart_order', 'INNER')
				->on('cart_order.id', '=', 'cart_order_product.cart_order_id')
				->where('cart_order.id', 'IN', $orderIds)
				->where_expiry('cart_order')
				->find_all();
			foreach ($orderProducts as $orderProduct) {
				++ $row_num;

				$order = $orderProduct->cart_order;
				$order->set_mode('view');

				$paid_log = $order->cart_order_log
					->where('cart_order_log.action', '=', 'paid')
					->find();
				$payment_transaction = $order->payment();
				$charge_id = $payment_transaction->transaction_id;

				$row_data = array(
					$order->order_num,
					$order->get_field('status'),
					$paid_log->timestamp,
					$orderProduct->unit_price,
					$payment_transaction->response['card']['type'] . ' ' . $payment_transaction->response['card']['last4'],
				);

				$row_data[] = $order->billing_first_name . ' ' . $order->billing_last_name;
				$row_data[] = XM::format_phone($order->billing_phone);
				$row_data[] = $order->billing_email;
				$row_data[] = $order->billing_address_1;
				$row_data[] = $order->billing_address_2;
				$row_data[] = $order->billing_municipality;
				$row_data[] = $order->billing_state_select->name;
				$row_data[] = $order->billing_postal_code;

				$charge_data = Cart_Transfer::get_transaction($charge_id);

				if ( ! empty($charge_data)) {
					$row_data[] = $charge_data['transfer_id'];
					$transfer_datetime = (new DateTime('@'.$charge_data['transfer_date']));
					$row_data[] = $transfer_datetime->format('Y-m-d');
				} else {
					$row_data[] = '';
					$row_data[] = '';
				}

				$row_data[] = $order->order_note;

				XLS::add_row($donationSheet, $row_num, $row_data);
			}

			// formatting for the amount col
			$donationSheet->getStyle('D4:D' . $row_num)
				->getNumberFormat()
				->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
			// wrap the notes col
			$donationSheet->getStyle('P4:P' . $row_num)
				->getAlignment()->setWrapText(true);
		}

		// ************ Finalization ***************
		$xlsx->setActiveSheetIndex(0);
		$temp_xls_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'orders-' . Auth::instance()->get_user()->id . '-' . time() . '.xlsx';
		$output = PHPExcel_IOFactory::createWriter($xlsx, 'Excel2007');
		$output->save($temp_xls_file);

		$this->response->send_file($temp_xls_file, 'Orders.xlsx', array('delete' => TRUE));
	}

	protected function setSheetTitle($sheet, $name, $lastMergeCol) {
		$sheet->setTitle($name);

		$sheet->setCellValueExplicit('A1', $name);
		$sheet->getStyle('A1')->getFont()->setBold(TRUE)->setSize(17);
		$sheet->mergeCells('A1:'.$lastMergeCol.'1');
	}
}