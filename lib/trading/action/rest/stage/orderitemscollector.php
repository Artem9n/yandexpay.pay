<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use YandexPay\Pay\Data\Measure;
use YandexPay\Pay\Data\Vat;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderItemsCollector extends ResponseCollector
{
	public function __invoke(State\OrderCalculation $state)
	{
		$result = [];

		foreach ($state->order->getOrderableItems() as $basketCode)
		{
			$basketResult = $state->order->getBasketItemData($basketCode);
			$basketData = $basketResult->getData();

			$product = [
				(string)$basketData['PRODUCT_ID'],
				(string)$basketData['BASKET_ID']
			];

			$result[] = [
				'productId' => implode(':', $product),
				'unitPrice' => (float)$basketData['BASE_PRICE'],
				'discountedUnitPrice' => (float)$basketData['PRICE'],
				'subtotal' => (float)$basketData['TOTAL_BASE_PRICE'],
				'total' => (float)$basketData['TOTAL_PRICE'],
				'title' => (string)$basketData['NAME'],
				'quantity' => [
					'count' => (float)$basketData['QUANTITY'],
					'label' => (string)$basketData['MEASURE_NAME'],
					'measure' => Measure::convertForService($basketData['MEASURE_CODE']),
				],
				'receipt' => [
					'tax' => Vat::convertForService($basketData['VAT_RATE'])
				],
				'measurements' => [
					'weight' => $basketData['WEIGHT'],
					'height' => $basketData['HEIGHT'],
					'length' => $basketData['LENGTH'],
					'width' => $basketData['WIDTH']
				]
			];
		}

		$this->write($result);
	}
}

