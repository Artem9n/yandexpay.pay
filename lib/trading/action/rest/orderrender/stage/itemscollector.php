<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderRender\Stage;

use YandexPay\Pay\Data\Vat;
use YandexPay\Pay\Data\Measure;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Action\Rest\Stage\ResponseCollector;

class ItemsCollector extends ResponseCollector
{
	/** @var array */
	protected $items = [];

	public function __invoke(State\Order $state)
	{
		$this->basketCollector($state);
		$this->deliveryCollector($state);

		$this->write($this->items);
	}

	protected function basketCollector(State\Order $state) : void
	{
		/** @var \Bitrix\Sale\BasketItem $basketItem */
		foreach ($state->basket as $basketItem)
		{
			$product = [
				(string)$basketItem->getProductId(),
				(string)$basketItem->getBasketCode(),
			];

			$dimensions = unserialize($basketItem->getField('DIMENSIONS'), [false]);

			$this->items[] = [
				'productId' => implode(':', $product),
				'unitPrice' => (float)$basketItem->getBasePriceWithVat(),
				'discountedUnitPrice' => (float)$basketItem->getPriceWithVat(),
				'subtotal' => (float)$basketItem->getBasePriceWithVat() * $basketItem->getQuantity(),
				'total' => (float)$basketItem->getFinalPrice(),
				'title' => (string)$basketItem->getField('NAME'),
				'quantity' => [
					'count' => (float)$basketItem->getQuantity(),
					'label' => (string)$basketItem->getField('MEASURE_NAME'),
					'measure' => Measure::convertForService($basketItem->getField('MEASURE_CODE')),
				],
				'receipt' => [
					'tax' => Vat::convertForService($basketItem->getVatRate()),
				],
				'measurements' => [
					'weight' => $basketItem->getWeight() / 1000,
					'height' => $dimensions['HEIGHT'] / 1000,
					'length' => (float)$dimensions['LENGTH'],
					'width' => (float)$dimensions['WIDTH'],
				],
			];
		}
	}

	protected function deliveryCollector(State\Order $state) : void
	{
		$this->items[] = [
			'productId' => (string)$state->delivery->getId(),
			'unitPrice' => (float)$state->order->getDeliveryPrice(),
			'discountedUnitPrice' => (float)$state->order->getDeliveryPrice(),
			'subtotal' => (float)$state->order->getDeliveryPrice(),
			'total' => (float)$state->order->getDeliveryPrice(),
			'title' => $state->delivery->getNameWithParent(),
			'receipt' => [
				'tax' => Vat::convertForService($state->delivery->getVatId()),
			],
			'quantity' => [
				'count' => 1,
			],
		];
	}
}

