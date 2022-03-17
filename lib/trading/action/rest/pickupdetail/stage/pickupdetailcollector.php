<?php
namespace YandexPay\Pay\Trading\Action\Rest\PickupDetail\Stage;

use YandexPay\Pay\Trading\Action\Rest\PickupDetail;
use YandexPay\Pay\Trading\Action\Rest\PickupOptions\Stage;
use YandexPay\Pay\Trading\Action\Rest\Reference\EffectiveResponse;
use YandexPay\Pay\Trading\Action\Rest\State;

class PickupDetailCollector extends Stage\PickupOptionsCollector
{
	protected $request;

	public function __construct(EffectiveResponse $response, PickupDetail\Request $request, string $key = '')
	{
		parent::__construct($response, $key);

		$this->request = $request;
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$state->order->setLocation($this->request->getLocationId());
		$calculationResult = $state->environment->getDelivery()->calculate($this->request->getPickupId(), $state->order);

		if (!$calculationResult->isSuccess()) { return; }

		$store = $state->environment->getDelivery()->getStore($this->request->getStoreId());

		$result = $this->collectPickupOption($calculationResult, $store, $this->request->getLocationId());

		$this->write($result);
	}
}

