<?php defined('SYSPATH') OR die('No direct script access.');

/**
 *
 *
 * @package    XM Cart
 * @category   Models
 * @author     XM Media Inc.
 * @copyright  (c) 2014 XM Media Inc.
 */
class XM_Cart_ORM extends ORM {
	public function where_active_dates() {
		return $this
			->where_open()
				->or_where_open()
					->where('start', '<=', DB::expr("NOW()"))
					->where('end', '>=', DB::expr("NOW()"))
				->or_where_close()
				->or_where_open()
					->where('start', '<=', DB::expr("NOW()"))
					->where('end', '=', 0)
				->or_where_close()
				->or_where_open()
					->where('start', '=', 0)
					->where('end', '>=', DB::expr("NOW()"))
				->or_where_close()
				->or_where_open()
					->where('start', '=', 0)
					->where('end', '=', 0)
				->or_where_close()
			->where_close();
	}
}