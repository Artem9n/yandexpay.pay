<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Stage;

use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Action\Rest;

class DeliveryProperties
{
	protected $address;

	public function __construct(Rest\Dto\Address $address)
	{
		$this->address = $address;
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$state->order->fillPropertiesCourier(
			$this->address->getMeaningfulAddress(),
			$this->address->getMeaningfulZip()
		);
	}
}