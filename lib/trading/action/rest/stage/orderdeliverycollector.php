<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use Bitrix\Main;
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
		$result = [];

		$deliveries = $this->restrictedDeliveries($state);
		$deliveries = $this->filterDeliveryByType($state, $deliveries, EntitySale\Delivery::DELIVERY_TYPE);
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

			$result[] = $this->collectDeliveryOption($calculationResult);
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

	protected function restrictedDeliveries(State\OrderCalculation $state) : array
	{
		$result = [];
		$deliveryService = $state->environment->getDelivery();
		$compatibleIds = $deliveryService->getRestricted($state->order);

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

	protected function collectDeliveryOption(EntityReference\Delivery\CalculationResult $calculationResult) : array
	{
		$toDate = $calculationResult->getDateTo();

		return [
			'courierOptionId' => (string)$calculationResult->getDeliveryId(),
			'provider' => 'COURIER', //todo # Идентификатор службы доставки. enum<COURIER|CDEK|EMS|DHL>
			'category' => $calculationResult->getCategory(), // enum<EXPRESS|TODAY|STANDARD>
			'title' => $calculationResult->getServiceName(),
			'amount'    => (float)$calculationResult->getPrice(),
			'fromDate' => $calculationResult->getDateFrom()->format('Y-m-d'),
			'toDate' => $toDate !== null ? $toDate->format('Y-m-d') : null,
		];
	}
}

