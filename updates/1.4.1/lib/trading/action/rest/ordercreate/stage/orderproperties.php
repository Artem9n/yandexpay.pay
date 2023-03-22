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
		$this->fillDeliveryDateTime($state);
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
		$address = $this->request->getAddress();

		if ($address === null) { return; }

		$comment = $address->getComment();

		if ((string)$comment !== '' && $state->options->useComment())
		{
			$state->order->setComment($comment);
		}
	}

	protected function fillDeliveryDateTime(State\OrderCalculation $state) : void
	{
		$deliveryRequest = $this->request->getDelivery();
		$scheduleType = $deliveryRequest->getScheduleType();

		if ($scheduleType === 'PLAIN')
		{
			$deliveryDate = $deliveryRequest->getPlainToDate() ?? $deliveryRequest->getPlainFromDate();
			$deliveryFromTime = $deliveryRequest->getPlainFromTime();
			$deliveryToTime = $deliveryRequest->getPlainToTime();
			$valueTime = $deliveryFromTime !== null && $deliveryToTime !== null
				? $deliveryFromTime . ' - ' . $deliveryToTime
				: '';
		}
		else
		{
			$deliveryDate = $deliveryRequest->getCustomerChoiceDate();
			$deliveryTime = $deliveryRequest->getCustomerChoiceTime();

			if ($deliveryDate === null && $deliveryTime === null) { return; }

			$valueTime = $deliveryTime !== null ? $deliveryTime['start'] . ' - ' . $deliveryTime['end'] : '';
		}

		Utils\OrderProperties::setMeaningfulPropertyValues($state, [
			'DATE_DELIVERY' => trim(sprintf('%s %s', $deliveryDate, $valueTime)),
		]);
	}
}