<?php
namespace YandexPay\Pay\Trading\Action\Rest\PickupDetail\Stage;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Action\Reference\Exceptions\DtoProperty;
use YandexPay\Pay\Trading\Action\Rest\PickupDetail;
use YandexPay\Pay\Trading\Action\Rest\Reference\EffectiveResponse;
use YandexPay\Pay\Trading\Action\Rest\Stage\ResponseCollector;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;
use YandexPay\Pay\Trading\Entity\Sale\Delivery;

class PickupDetailCollector extends ResponseCollector
{
	use Concerns\HasMessage;

	protected $request;
	protected $deliveryId;
	protected $storeId;
	protected $locationId;
	protected $zip;

	public function __construct(EffectiveResponse $response, PickupDetail\Request $request, string $key = '')
	{
		parent::__construct($response, $key);

		$this->request = $request;
		$this->deliveryId = $request->getPickupId();
		$this->storeId = $request->getStoreId();
		$this->locationId = $request->getLocationId();
		$this->zip = $request->getZip();
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$deliveryService = $state->environment->getDelivery()->getDeliveryService($this->deliveryId);
		$pickup = Delivery\Factory::make($deliveryService, Delivery::PICKUP_TYPE);

		$pickup->prepareCalculatePickup(
			$state->order->getCalculatable(),
			$this->deliveryId,
			$this->storeId,
			$this->locationId,
			$this->zip
		);

		$state->order->clearCalculatable();

		$state->order->setLocation($this->request->getLocationId());

		$isComatible = $state->environment->getDelivery()->isCompatible($this->deliveryId, $state->order);
		$deliveryName = $deliveryService->getNameWithParent();

		if (!$isComatible)
		{
			$message = self::getMessage('PICKUP_NOT_COMPATIBLE', [
				'#STORE_ID#' => $this->storeId,
				'#DELIVERY_ID#' => $this->deliveryId,
				'#NAME#' => $deliveryName
			]);

			throw new DtoProperty($message);
		}

		$calculationResult = $state->environment->getDelivery()->calculate($this->deliveryId, $state->order);

		if (!$calculationResult->isSuccess())
		{
			$message = self::getMessage('PICKUP_NOT_CALCULATE', [
				'#STORE_ID#' => $this->storeId,
				'#DELIVERY_ID#' => $this->deliveryId,
				'#NAME#' => $deliveryName,
				'#ERROR_MESSAGES#' => implode(', ', $calculationResult->getErrorMessages()),
			]);

			throw new DtoProperty($message);
		}

		$store = $pickup->getDetailPickup($this->storeId);
		$result = $this->collectPickupOption($calculationResult, $store, $this->locationId);

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

