<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Stage;

use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Action\Rest\OrderCreate;

class PickupProperties
{
	protected $pickup;

	public function __construct(OrderCreate\Dto\Pickup $request)
	{
		$this->pickup = $request;
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$state->order->fillPropertiesStore($this->pickup->getStoreId(), $this->pickup->getAddress());
	}
}