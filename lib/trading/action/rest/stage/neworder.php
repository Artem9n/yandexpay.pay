<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Exceptions;

class NewOrder
{
	protected $items;
	protected $userId;
	protected $currency;
	protected $coupons;

	public function __construct(int $userId = null, string $currency = null, array $coupons = null)
	{
		$this->userId = $userId;
		$this->currency = $currency;
		$this->coupons = $coupons;
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$this->makeOrder($state);
		$this->fillPersonType($state);
		$this->fillCoupons($state);
	}

	protected function makeOrder(State\OrderCalculation $state) : void
	{
		$state->order = $state->environment->getOrderRegistry()->createOrder(
			$state->setup->getSiteId(),
			$state->userId ?? $this->userId,
			$this->currency
		);
	}

	protected function fillPersonType(State\OrderCalculation $state) : void
	{
		$personTypeResult = $state->order->setPersonType($state->setup->getPersonTypeId());

		Exceptions\Facade::handleResult($personTypeResult);
	}

	protected function fillCoupons(State\OrderCalculation $state) : void
	{
		$coupons = Sale\DiscountCouponsManager::get(true, [], true, true);

		foreach ($coupons as $coupon)
		{
			Sale\DiscountCouponsManager::delete($coupon['COUPON']);
		}

		if ($this->coupons === null) { return; }

		foreach ($this->coupons as $coupon)
		{
			$value = $coupon['value'];

			if ($value === null || trim($value) === '') { continue; }

			$state->order->applyCoupon($value);
		}
	}
}

