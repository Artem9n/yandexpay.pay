<?php
namespace YandexPay\Pay\Trading\Action\Rest\PickupDetail\Stage;

use YandexPay\Pay\Trading\Action\Rest\PickupDetail;
use YandexPay\Pay\Trading\Action\Rest\Reference\EffectiveResponse;
use YandexPay\Pay\Trading\Action\Rest\Stage\ResponseCollector;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;

class PickupDetailCollector extends ResponseCollector
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

	protected function collectPickupOption(EntityReference\Delivery\CalculationResult $calculationResult, array $store, int $locationId = null) : array
	{
		return [
			'pickupPointId' => implode(':', [$calculationResult->getDeliveryId(), $store['ID'], $locationId]), //todo send need metadata
			'provider' => 'IN_STORE', //todo enum<PICKPOINT|...|IN_STORE>
			'address' => $store['ADDRESS'],
			'location' =>  [
				'latitude' => (float)$store['GPS_N'],
				'longitude' => (float)$store['GPS_S'],
			],
			'title'     => $store['TITLE'],
			'fromDate' => $calculationResult->getDateFrom()->format('Y-m-d'),
			'toDate' => $calculationResult->getDateTo()->format('Y-m-d'),
			'amount' => (float)$calculationResult->getPrice(),
			'description' => $store['DESCRIPTION'],
			'phones' => explode(', ', $store['PHONE']),
		];
	}
}

