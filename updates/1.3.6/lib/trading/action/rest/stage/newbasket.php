<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use Bitrix\Main;
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
		[$exists, $existsMap, $new] = $state->order->isSaved()
			? $this->splitOrderAlreadyExists($state)
			: $this->splitBasketAlreadyExists();

		if ($state->order->isSaved())
		{
			[$notFound, $needDelete] = $this->syncBasketExistProducts($state, $exists, $existsMap);

			$this->deleteBasketProducts($state, $needDelete);
			$this->addBasketNewProducts($state, $notFound);
		}
		else if (!empty($exists))
		{
			$state->order->initUserBasket();

			[$notFound, $needDelete] = $this->syncBasketExistProducts($state, $exists, $existsMap);

			$this->deleteBasketProducts($state, $needDelete);
			$this->addBasketNewProducts($state, $notFound);
		}
		else
		{
			$state->order->initEmptyBasket();
		}

		$this->addBasketNewProducts($state, $new);
	}

	protected function splitOrderAlreadyExists(State\OrderCalculation $state) : array
	{
		$basketMap = array_flip($state->order->getOrderableItems());
		$exists = [];
		$existsMap = [];
		$new = [];

		foreach ($this->items as $index => $product)
		{
			$basketId = (int)$product->getBasketId();

			if ($basketId > 0 && isset($basketMap[$basketId]))
			{
				$exists[$index] = $product;
				$existsMap[$index] = $basketId;
				unset($basketMap[$basketId]);
			}
			else if (($searchBasketCode = $this->searchBasketProduct($state, $product, array_keys($basketMap))) !== null)
			{
				$exists[$index] = $product;
				$existsMap[$index] = $searchBasketCode;
				unset($basketMap[$searchBasketCode]);
			}
			else
			{
				$new[$index] = $product;
			}
		}

		return [$exists, $existsMap, $new];
	}

	protected function searchBasketProduct(State\OrderCalculation $state, Cart\Item $item, array $basketCodes) : ?string
	{
		foreach ($basketCodes as $basketCode)
		{
			$basketData = $state->order->getBasketItemData($basketCode)->getData();

			if ((string)$basketData['PRODUCT_ID'] === $item->getProductId())
			{
				return (string)$basketCode;
			}
		}

		return null;
	}

	protected function splitBasketAlreadyExists() : array
	{
		$exists = [];
		$existsMap = [];
		$new = [];

		foreach ($this->items as $index => $product)
		{
			if ((int)$product->getBasketId() > 0)
			{
				$exists[$index] = $product;
				$existsMap[$index] = $product->getBasketId();
			}
			else
			{
				$new[$index] = $product;
			}
		}

		return [$exists, $existsMap, $new];
	}

	protected function addBasketNewProducts(State\OrderCalculation $state, array $products) : void
	{
		/** @var Cart\Item $product */
		foreach ($products as $index => $product)
		{
			$productId = $product->getProductId();
			$quantity = $product->getCount();
			$basketData = $this->getProductBasketData($state, $productId);

			$addResult = $state->order->addProduct($productId, $quantity, $basketData);
			$addData = $addResult->getData();

			Exceptions\Facade::handleResult($addResult, DtoProperty::class);
			Assert::notNull($addData['BASKET_CODE'], '$addData[BASKET_CODE]');

			$state->basketMap[$index] = $addData['BASKET_CODE'];
		}
	}

	protected function syncBasketExistProducts(State\OrderCalculation $state, array $products, array $existsMap) : array
	{
		$existsCodes = $state->order->getOrderableItems();
		$notFound = [];
		$needDelete = array_diff($existsCodes, $existsMap);

		/** @var Cart\Item $product */
		foreach ($products as $index => $product)
		{
			$basketCode = $existsMap[$index] ?? null;

			if (empty($basketCode))
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

	protected function getProductBasketData(State\OrderCalculation $state, int $productId) : array
	{
		$environmentProduct = $state->environment->getProduct();
		$fewData = $environmentProduct->getBasketData([$productId]);
		$itemData = $fewData[$productId] ?? null;

		Assert::notNull($itemData, '$environmentProduct->getBasketData()');

		if (isset($itemData['ERROR']))
		{
			throw new Main\SystemException($itemData['ERROR']);
		}

		return $itemData;
	}
}

