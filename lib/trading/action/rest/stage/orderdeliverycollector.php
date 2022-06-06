<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;

class OrderDeliveryCollector extends ResponseCollector
{
	public function __invoke(State\OrderCalculation $state)
	{
		$result = [];

		$deliveries = $this->restrictedDeliveries($state);
		$deliveries = $this->filterDeliveryByType($state, $deliveries, EntitySale\Delivery::DELIVERY_TYPE);

		foreach ($deliveries as $deliveryId)
		{
			if (!$state->environment->getDelivery()->isCompatible($deliveryId, $state->order)) { continue; }

			$calculationResult = $state->environment->getDelivery()->calculate($deliveryId, $state->order);

			if (!$calculationResult->isSuccess()) { continue; }

			$result[] = $this->collectDeliveryOption($calculationResult);
		}

		$this->write($result);
	}

	protected function restrictedDeliveries(State\OrderCalculation $state, int $mode = Sale\Delivery\Restrictions\Manager::MODE_CLIENT) : array
	{
		$result = [];
		$deliveryService = $state->environment->getDelivery();
		$compatibleIds = $deliveryService->getRestricted($state->order, $mode);

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
		return [
			'courierOptionId' => (string)$calculationResult->getDeliveryId(),
			'provider' => 'COURIER', //todo # Идентификатор службы доставки. enum<COURIER|CDEK|EMS|DHL>
			'category' => $calculationResult->getCategory(), // enum<EXPRESS|TODAY|STANDARD>
			'title' => $calculationResult->getServiceName(),
			'amount'    => (float)$calculationResult->getPrice(),
			'fromDate' => $calculationResult->getDateFrom()->format('Y-m-d'),
			'toDate' => $calculationResult->getDateTo()->format('Y-m-d'),
			//'fromTime' => '', //todo HH:mm
			//'toTime' => '', //todo HH:mm
		];
	}
}

