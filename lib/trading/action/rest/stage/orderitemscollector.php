<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

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

			$result[] = [
				'productId' => implode(':', [
					(string)$basketData['PRODUCT_ID'],
					$basketData['BASKET_ID'] ?? null]
				),
				'unitPrice' => (float)$basketData['BASE_PRICE'],
				'discountedUnitPrice' => (float)$basketData['PRICE'],
				'subtotal' => (float)$basketData['TOTAL_BASE_PRICE'],
				'total' => (float)$basketData['TOTAL_PRICE'],
				'title' => (string)$basketData['NAME'],
				'quantity' => [
					'count' => (float)$basketData['QUANTITY'],
					//'available' => (float), todo
					//'label' => (string), todo
				],
			];
		}

		$this->write($result);
	}
}

