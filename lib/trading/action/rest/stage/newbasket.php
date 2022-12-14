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
		[$exists, $new] = $state->order->isSaved()
			? $this->splitOrderAlreadyExists($state)
			: $this->splitBasketAlreadyExists();

		if ($state->order->isSaved())
		{
			[$notFound, $needDelete] = $this->syncBasketExistProducts($state, $exists);

			$this->deleteBasketProducts($state, $needDelete);
			$this->addBasketNewProducts($state, $notFound);
		}
		else if (!empty($exists))
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

	protected function splitOrderAlreadyExists(State\OrderCalculation $state)
	{
		$basketMap = array_flip($state->order->getOrderableItems());
		$exists = [];
		$new = [];

		foreach ($this->items as $index => $product)
		{
			$basketId = (int)$product->getBasketId();

			if ($basketId > 0 && isset($basketMap[$basketId]))
			{
				$exists[$index] = $product;
				unset($basketMap[$basketId]);
			}
			else if ($this->searchBasketProduct($state, $product, array_keys($basketMap)) !== null)
			{
				$exists[$index] = $product;
				unset($basketMap[$basketId]);
			}
			else
			{
				$new[$index] = $product;
			}
		}

		return [$exists, $new];
	}

	protected function searchBasketProduct(State\OrderCalculation $state, Cart\Item $item, array $basketCodes) : ?string
	{
		$result = null;

		foreach ($basketCodes as $basketCode)
		{
			$basketData = $state->order->getBasketItemData($basketCode)->getData();

			if ((string)$basketData['PRODUCT_ID'] === (string)$item->getProductId())
			{
				$result = $basketCode;
				break;
			}
		}

		return $result;
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
			$basketData = $this->getProductBasketData($state, $productId);

			$addResult = $state->order->addProduct($productId, $quantity, $basketData);
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

	protected function getProductBasketData(State\OrderCalculation $state, int $productId) : array
	{
		$environmentProduct = $state->environment->getProduct();
		$fewData = $environmentProduct->getBasketData([$productId]);
		$itemData = $fewData[$productId] ?? null;

		Assert::notNull($itemData, '$enviromentProduct->getBasketData()');

		if (isset($itemData['ERROR']))
		{
			throw new Main\SystemException($itemData['ERROR']);
		}

		return $itemData;
	}
}

