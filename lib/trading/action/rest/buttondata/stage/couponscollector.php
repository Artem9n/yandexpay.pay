<?php
namespace YandexPay\Pay\Trading\Action\Rest\ButtonData\Stage;

use YandexPay\Pay\Trading\Action\Rest\Stage;
use YandexPay\Pay\Trading\Action\Rest\State;

class CouponsCollector extends Stage\CouponsCollector
{
	public function __invoke(State\OrderCalculation $state) : void
	{
		$result = [];

		foreach ($this->coupons() as $coupon)
		{
			if ($coupon['status'] !== 'VALID') { continue; }

			$result[] = [
				'value' => $coupon['value'],
			];
		}

		$this->write($result);
	}
}