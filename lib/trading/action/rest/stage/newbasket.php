<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use YandexPay\Pay\Trading\Action\Reference\Exceptions\DtoProperty;
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
			$state->order->initUserBasket();

			[$notFound, $needDelete] = $this->syncBasketExistProducts($state, $exists);

			$this->deleteBasketProducts($state, $needDelete);
			$this->addBasketNewProducts($state, $notFound);
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

			Exceptions\Facade::handleResult($addResult, DtoProperty::class);
			Assert::notNull($addData['BASKET_CODE'], '$addData[BASKET_CODE]');

			$state->basketMap[$index] = $addData['BASKET_CODE'];
		}
	}

	protected function syncBasketExistProducts(State\OrderCalculation $state, array $products) : array
	{
		$productCodes = array_map(static function(Cart\Item $item) { return $item->getBasketId(); }, $products);
		$existsCodes = $state->order->getOrderableItems();
		$existsMap = array_flip($existsCodes);
		$notFound = [];
		$needDelete = array_diff($existsCodes, $productCodes);

		/** @var Cart\Item $product */
		foreach ($products as $index => $product)
		{
			$basketCode = $product->getBasketId();

			if (!isset($existsMap[$basketCode]))
			{
				$notFound[$index] = $product;
				continue;
			}

			$quantityResult = $state->order->setBasketItemQuantity($basketCode, $product->getCount());

			Exceptions\Facade::handleResult($quantityResult, DtoProperty::class);

			$state->basketMap[$index] = $basketCode;
		}

		return [$notFound, $needDelete];
	}

	protected function deleteBasketProducts(State\OrderCalculation $state, array $basketCodes) : void
	{
		foreach ($basketCodes as $basketCode)
		{
			$result = $state->order->deleteBasketItem($basketCode);
			Exceptions\Facade::handleResult($result, DtoProperty::class);
		}
	}
}

