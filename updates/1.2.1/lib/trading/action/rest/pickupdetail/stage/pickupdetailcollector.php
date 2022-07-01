<?php
namespace YandexPay\Pay\Trading\Action\Rest\PickupDetail\Stage;

use YandexPay\Pay\Trading\Action\Reference\Exceptions\DtoProperty;
use YandexPay\Pay\Trading\Action\Rest\PickupDetail;
use YandexPay\Pay\Trading\Action\Rest\Reference\EffectiveResponse;
use YandexPay\Pay\Trading\Action\Rest\Stage\ResponseCollector;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;
use YandexPay\Pay\Trading\Entity\Sale\Delivery;

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
		$deliveryService = $state->environment->getDelivery()->getDeliveryService($this->request->getPickupId());
		$pickup = Delivery\Factory::make($deliveryService, 'pickup');
		$pickup->prepareCalculatePickup($this->request->getPickupId(), $this->request->getStoreId(), $this->request->getLocationId(), $this->request->getZip());

		$state->order->clearCalculatable();

		$state->order->setLocation($this->request->getLocationId());

		$calculationResult = $state->environment->getDelivery()->calculate($this->request->getPickupId(), $state->order);

		if (!$calculationResult->isSuccess())
		{
			throw new DtoProperty(implode(', ', $calculationResult->getErrorMessages()), 'OTHER');
		}

		$store = $pickup->getDetailPickup($this->request->getStoreId());

		$result = $this->collectPickupOption($calculationResult, $store, $this->request->getLocationId());

		$this->write($result);
	}

	protected function collectPickupOption(EntityReference\Delivery\CalculationResult $calculationResult, array $store, int $locationId = null) : array
	{
		$toDate = $calculationResult->getDateTo();

		return [
			'pickupPointId' => implode(':', [$calculationResult->getDeliveryId(), $store['ID'], $locationId, $store['ZIP']]),
			'provider' => $store['PROVIDER'] ?? 'IN_STORE', //todo enum<PICKPOINT|...|IN_STORE>
			'address' => $store['ADDRESS'],
			'location' =>  [
				'latitude' => (float)$store['GPS_N'],
				'longitude' => (float)$store['GPS_S'],
			],
			'title' => $store['TITLE'],
			'fromDate' => $calculationResult->getDateFrom()->format('Y-m-d'),
			'toDate' => $toDate !== null ? $toDate->format('Y-m-d') : null,
			'amount' => (float)$calculationResult->getPrice(),
			'description' => $store['DESCRIPTION'],
			'phones' => explode(', ', $store['PHONE']),
		];
	}
}

