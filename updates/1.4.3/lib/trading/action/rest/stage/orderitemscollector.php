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
			$basketData = $state->order->getBasketItemData($basketCode)->getData();

			$product = [
				(string)$basketData['PRODUCT_ID'],
				(string)$basketData['BASKET_ID']
			];

			$result[] = [
				'type' => 'PHYSICAL',//enum<PHYSICAL|DIGITAL|UNSPECIFIED>, todo type item
				'productId' => implode(':', $product),
				'unitPrice' => (float)$basketData['BASE_PRICE'],
				'discountedUnitPrice' => (float)$basketData['BASE_PRICE'],
				'subtotal' => (float)$basketData['TOTAL_BASE_PRICE'],
				'total' => (float)$basketData['TOTAL_BASE_PRICE'],
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
					'weight' => (float)$basketData['WEIGHT'],
					'height' => (float)$basketData['HEIGHT'],
					'length' => (float)$basketData['LENGTH'],
					'width' => (float)$basketData['WIDTH'],
				]
			];
		}

		$this->write($result);
	}
}

