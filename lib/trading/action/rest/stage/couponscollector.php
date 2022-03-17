<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Action\Rest\State;

class CouponsCollector extends ResponseCollector
{
	public function __invoke(State\OrderCalculation $state) : void
	{
		$result = [];

		$coupons = Sale\DiscountCouponsManager::get(true, [], true, true);

		if (empty($coupons)) { return; }

		foreach ($coupons as $coupon)
		{
			$isFind = false;

			$status = 'VALID';

			if ($coupon['STATUS'] === Sale\DiscountCouponsManager::STATUS_FREEZE)
			{
				$status = 'EXPIRED';
			}
			else if ($coupon['STATUS'] === Sale\DiscountCouponsManager::STATUS_NOT_FOUND)
			{
				$status = 'INVALID';
			}
			else
			{
				$isFind = true;
			}

			$result[] = [
				'value' => $coupon['COUPON'],
				'status' => $status,
				'description' => $isFind ? $coupon['DISCOUNT_NAME'] : $coupon['STATUS_TEXT'],
			];
		}

		$this->write($result);
	}
}

