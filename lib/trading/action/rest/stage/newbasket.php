<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use YandexPay\Pay\Trading\Action\Rest\Dto\Cart;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Reference\Assert;

class NewBasket
{
	protected $items;

	public function __construct(Cart\Items $items)
	{
		$this->items = $items;
	}

	public function __invoke(State\OrderCalculation $state)
	{
		[$exists, $new] = $this->splitBasketAlreadyExists();

		if (!empty($exists))
		{
			//todo
		}
		else
		{
			$state->order->initEmptyBasket();
		}

		$this->addBasketNewProducts($state, $new);
	}

	protected function splitBasketAlreadyExists() : array
	{
		$exists = [];
		$new = [];

		foreach ($this->items as $index => $product)
		{
			if ((int)$product->getBasketId() > 0)
			{
				$exists[$index] = $product;
			}
			else
			{
				$new[$index] = $product;
			}
		}

		return [$exists, $new];
	}

	protected function addBasketNewProducts(State\OrderCalculation $state, array $products) : void
	{
		/** @var Cart\Item $product */
		foreach ($products as $index => $product)
		{
			$productId = $product->getProductId();
			$quantity = $product->getCount();

			$addResult = $state->order->addProduct($productId, $quantity);
			$addData = $addResult->getData();

			Exceptions\Facade::handleResult($addResult);
			Assert::notNull($addData['BASKET_CODE'], '$addData[BASKET_CODE]');

			$state->basketMap[$index] = $addData['BASKET_CODE'];
		}
	}
}

