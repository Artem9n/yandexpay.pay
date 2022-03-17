<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use YandexPay\Pay\Trading\Action\Rest\Dto;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Trading\Action\Rest\Utils;

class OrderLocation
{
	/** @var Dto\Address|null  */
	protected $address;

	public function __construct(Dto\Address $address = null)
	{
		$this->address = $address;
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$this->fillLocation($state);
	}

	protected function fillLocation(State\OrderCalculation $state) : void
	{
		if ($this->address === null) { return; }

		$locationService = $state->environment->getLocation();
		$locationId = $locationService->getLocation($this->address->getFields());
		$meaningfulValues = $locationService->getMeaningfulValues($locationId);

		$orderResult = $state->order->setLocation($locationId);

		Exceptions\Facade::handleResult($orderResult);

		if (!empty($meaningfulValues))
		{
			Utils\OrderProperties::setMeaningfulPropertyValues($state, $meaningfulValues);
		}
	}
}

