<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Action\Rest\State;

class CouponsCollector extends ResponseCollector
{
	public function __invoke(State\OrderCalculation $state) : void
	{
		$this->write($this->coupons());
	}

	protected function coupons() : array
	{
		$result = [];

		$coupons = Sale\DiscountCouponsManager::get(true, [], true, true);

		if (empty($coupons)) { return []; }

		foreach ($coupons as $coupon)
		{
			$isFind = false;

			$status = 'VALID';

			if ($coupon['STATUS'] === Sale\DiscountCouponsManagerBase::STATUS_FREEZE)
			{
				$status = 'EXPIRED';
			}
			else if (
				in_array($coupon['STATUS'], [
					Sale\DiscountCouponsManagerBase::STATUS_NOT_FOUND,
					Sale\DiscountCouponsManagerBase::STATUS_NOT_APPLYED,
					Sale\DiscountCouponsManagerBase::STATUS_ENTERED,
				], true)
			)
			{
				$status = 'INVALID';
			}
			else
			{
				$isFind = true;
			}

			if (isset($coupon['CHECK_CODE_TEXT']))
			{
				$coupon['STATUS_TEXT'] = is_array($coupon['CHECK_CODE_TEXT'])
					? implode(', ', $coupon['CHECK_CODE_TEXT'])
					: $coupon['CHECK_CODE_TEXT'];
			}

			$result[] = [
				'value' => $coupon['COUPON'],
				'status' => $status,
				'description' => $isFind ? $coupon['DISCOUNT_NAME'] : $coupon['STATUS_TEXT'],
			];
		}

		return $result;
	}
}

