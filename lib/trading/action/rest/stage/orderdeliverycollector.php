<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Data\Vat;
use YandexPay\Pay\Logger;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;

class OrderDeliveryCollector extends ResponseCollector
{
	use Concerns\HasMessage;

	public function __invoke(State\OrderCalculation $state)
	{
		$deliveries = $this->restrictedDeliveries($state);
		$pickupDeliveries = $this->filterDeliveryByType($state, $deliveries, EntitySale\Delivery::PICKUP_TYPE);
		$courierDeliveries = $this->filterDeliveryByType($state, $deliveries, EntitySale\Delivery::DELIVERY_TYPE);

		$this->pickupOptions($state, $pickupDeliveries);
		$this->courierOptions($state, $courierDeliveries);
	}

	protected function pickupOptions(State\OrderCalculation $state, array $deliveries) : void
	{
		$logMessages = [];
		$hasPickup = false;

		foreach ($deliveries as $deliveryId)
		{
			$deliveryService = $state->environment->getDelivery()->getDeliveryService($deliveryId);
			$deliveryName = $deliveryService->getNameWithParent();

			if (!$state->environment->getDelivery()->isCompatible($deliveryId, $state->order))
			{
				$logMessages[] = self::getMessage('DELIVERY_NOT_COMPATIBLE', [
					'#ID#' => $deliveryId,
					'#NAME#' => $deliveryName,
				]);

				continue;
			}

			$hasPickup = true;
			break;
		}

		if ($hasPickup)
		{
			$this->pushMethod('PICKUP');
		}

		$this->writeLogger($state, $logMessages);
	}

	protected function courierOptions(State\OrderCalculation $state, array $deliveries) : void
	{
		$result = [];
		$logMessages = [];

		$yandexDelivery = $state->environment->getDelivery()->getYandexDeliveryService();
		$yandexDeliveryId = $yandexDelivery !== null ? (int)$yandexDelivery->getId() : 0;

		foreach ($deliveries as $deliveryId)
		{
			$deliveryService = $state->environment->getDelivery()->getDeliveryService($deliveryId);
			$deliveryName = $deliveryService->getNameWithParent();

			if (!$state->environment->getDelivery()->isCompatible($deliveryId, $state->order))
			{
				$logMessages[] = self::getMessage('DELIVERY_NOT_COMPATIBLE', [
					'#ID#' => $deliveryId,
					'#NAME#' => $deliveryName,
				]);

				continue;
			}

			if ($yandexDeliveryId > 0 && (int)$deliveryId === $yandexDeliveryId) { continue; }

			$calculationResult = $state->environment->getDelivery()->calculate($deliveryId, $state->order);

			if (!$calculationResult->isSuccess())
			{
				$message = implode(', ', $calculationResult->getErrorMessages());

				$logMessages[] = self::getMessage('DELIVERY_NOT_CALCULATE', [
					'#ID#' => $deliveryId,
					'#NAME#' => $deliveryName,
					'#ERROR_MESSAGES#' => Main\Text\Encoding::convertEncodingToCurrent($message),
				]);

				continue;
			}

			$result[] = $this->collectDeliveryOption($state, $calculationResult, $deliveryService);
		}

		if (!empty($result))
		{
			$this->pushMethod('COURIER');
		}

		$this->writeLogger($state, $logMessages);
		$this->write($result);
	}

	protected function writeLogger(State\OrderCalculation $state, array $messages) : void
	{
		if (empty($messages)) { return; }

		$state->logger->warning(implode(PHP_EOL, $messages), [
			'AUDIT' => Logger\Audit::DELIVERY_COLLECTOR,
		]);
	}

	protected function restrictedDeliveries(State\OrderCalculation $state, bool $skipLocation = false) : array
	{
		$result = [];
		$deliveryService = $state->environment->getDelivery();
		$compatibleIds = $deliveryService->getRestricted($state->order, $skipLocation);

		if (empty($compatibleIds)) { return $result; }

		if ($state->options->isDeliveryStrict())
		{
			$deliveryOptions = $state->options->getDeliveryOptions();
			$configuredIds = $deliveryOptions->getServiceIds();

			$result = array_intersect($compatibleIds, $configuredIds);
		}
		else
		{
			$result = $compatibleIds;
		}

		return $result;
	}

	protected function filterDeliveryByType(State\OrderCalculation $state, array $deliveryIds, string $type) : array
	{
		if (empty($deliveryIds)) { return []; }

		$result = [];

		$deliveryOptions = $state->options->getDeliveryOptions();
		$deliveryService = $state->environment->getDelivery();

		foreach ($deliveryIds as $deliveryId)
		{
			$service = $deliveryOptions->getItemByServiceId($deliveryId);

			if ($service !== null)
			{
				$typeOption = $service->getType();
			}
			else
			{
				$typeOption = $deliveryService->suggestDeliveryType($deliveryId);
			}

			if ($typeOption !== $type) { continue; }

			$result[] = $deliveryId;
		}

		return $result;
	}

	protected function collectDeliveryOption(
		State\OrderCalculation $state,
		EntityReference\Delivery\CalculationResult $calculationResult,
		Sale\Delivery\Services\Base $service
	) : array
	{
		return [
			'courierOptionId' => (string)$calculationResult->getDeliveryId(),
			'provider' => 'COURIER',
			'category' => $calculationResult->getCategory(),
			'title' => $calculationResult->getServiceName(),
			'amount' => (float)$calculationResult->getPrice(),
			'receipt' => [
				'tax' => $this->collectDeliveryTax($service->getVatId()),
			],
		] + $this->collectDeliveryDateTime($state, $calculationResult, $service->getId());
	}

	protected function collectDeliveryTax(int $vatId) : int
	{
		$vatList = Vat::getVatList();
		$vatRate = $vatList[$vatId] ?? 0;

		return Vat::convertForService($vatRate);
	}

	protected function collectDeliveryDateTime(
		State\OrderCalculation $state,
		EntityReference\Delivery\CalculationResult $calculationResult,
		int $deliveryId
	) : array
	{
		$deliveryOption = $state->options->getDeliveryOptions()->getItemByServiceId($deliveryId);

		$serviceFromDate = $calculationResult->getDateFrom()->format('Y-m-d');
		$serviceToDate = $calculationResult->getDateTo() !== null ? $calculationResult->getDateTo()->format('Y-m-d') : null;

		$result = [
			'type' => 'PLAIN',
			'fromDate' => $serviceFromDate,
			'toDate' => $serviceToDate,
		];

		if ($deliveryOption === null) { return $result; }

		$courierOptions = $deliveryOption->getCourierOptions();
		$type = $courierOptions->getTypeSchedule();

		if ($type === null) { return $result; }

		$optionFromDate = $courierOptions->getDateInterval()->getFromDate();
		$optionToDate = $courierOptions->getDateInterval()->getToDate();

		$typeTime = $courierOptions->getTypeTimeInterval();

		$timeIntervals = [];

		if ($type === 'PLAIN')
		{
			$timeIntervals = [
				'fromTime' => $courierOptions->getTimeInterval()->getFromTime(),
				'toTime' => $courierOptions->getTimeInterval()->getToTime(),
			];
		}
		else if ($typeTime !== null)
		{
			$timeIntervals['timeIntervals'] = [
				'type' => $typeTime,
			];

			if ($typeTime === 'GRID')
			{
				$timeIntervals['timeIntervals'] += [
					'grid' => [
						'duration' => $courierOptions->getDuration(),
						'start' => $courierOptions->getStartTime(),
						'end' => $courierOptions->getEndTime(),
						'step' => $courierOptions->getStepTime(),
					]
				];
			}
			else
			{
				$values = array_map(static function($value) {
					return [
						'start' => $value['FROM_TIME'],
						'end' => $value['TO_TIME']
					];
				}, $courierOptions->getTimeIntervals()->getValues());

				$timeIntervals['timeIntervals'] += [
					'values' => $values
				];
			}
		}

		return [
			'type' => $type,
			'fromDate' => $optionFromDate ?? $serviceFromDate,
			'toDate' => $optionToDate ?? $serviceToDate,
		] + $timeIntervals;
	}

	protected function pushMethod(string $type) : void
	{
		$key = 'shipping.availableMethods';
		$configured = $this->response->getField($key) ?? [];

		if (in_array($type, $configured, true)) { return; }

		$configured[] = $type;

		$this->write($configured, $key);
	}
}

