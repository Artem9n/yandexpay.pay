<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Stage;

use YandexPay\Pay\Trading\Action\Rest\Utils;
use YandexPay\Pay\Trading\Action\Rest\OrderCreate\Request;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderProperties
{
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$this->fillBuyerProperties($state);

		if ($this->request->getDeliveryType() === 'PICKUP') { return; } //todo make const delivery type

		$this->fillAddress($state);
		$this->fillComment($state);
	}

	protected function fillBuyerProperties(State\OrderCalculation $state) : void
	{
		$buyer = $this->request->getUser();
		$values = $buyer->getMeaningfulValues();
		Utils\OrderProperties::setMeaningfulPropertyValues($state, $values);
	}

	protected function fillAddress(State\OrderCalculation $state) : void
	{
		$address = $this->request->getAddress();

		if ($address === null) { return; }

		$values = [
			'ZIP' => $address->getMeaningfulZip(),
			'CITY' => $address->getMeaningfulCity(),
			'ADDRESS' => $address->getMeaningfulAddress(),
			'LAT' => $address->getLat(),
			'LON' => $address->getLon(),
		];

		Utils\OrderProperties::setMeaningfulPropertyValues($state, $values);
	}

	protected function fillComment(State\OrderCalculation $state) : void
	{
		$comment = $this->request->getComment();

		if ((string)$comment !== '')
		{
			$state->order->setComment($comment);
		}
	}
}