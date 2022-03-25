<?php
namespace YandexPay\Pay\Trading\Action\Rest\ButtonData\Stage;

use YandexPay\Pay\Trading\Action\Rest\Stage;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderItemsCollector extends Stage\ResponseCollector
{
	public function __invoke(State\OrderCalculation $state)
	{
		$result = [];

		foreach ($state->order->getOrderableItems() as $basketCode)
		{
			$itemResult = $state->order->getBasketItemData($basketCode);
			$itemData = $itemResult->getData();

			if ($itemData['QUANTITY'] <= 0) { continue; }

			$result[] = [
				'id' => implode(':', [
					(string)$itemData['PRODUCT_ID'],
					$itemData['BASKET_ID'] ?? null
				]),
				'count' => (string)$itemData['QUANTITY'],
				'label' => (string)$itemData['NAME'],
				'amount' => (string)($itemData['PRICE'] * $itemData['QUANTITY']),
				'basketId' => $itemData['BASKET_ID'] ?? null,
				'props' => $itemData['PROPS'],
			];/*[
				'id' => $itemData['PRODUCT_ID'],
				'amount' => (float)$itemData['TOTAL_PRICE'],
				'label' => (string)$itemData['NAME'],
				'quantity' => [
					'count' => (string)$itemData['QUANTITY'],
				],
			];*/
		}

		$this->write($result);
	}
}

