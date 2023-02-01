<?php
namespace YandexPay\Pay\Trading\Action\Rest\ButtonData\Stage;

use YandexPay\Pay;
use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Trading\Action\Reference;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action\Rest\State;

class WakeUpBasket
{
	protected $productId;
	protected $mode;

	public function __construct(string $mode, int $productId = null)
	{
		$this->productId = $productId;
		$this->mode = $mode;
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$resolveModeElement = Pay\Injection\Behavior\Registry::resolveModeElement();
		$resolveModeBasket = Pay\Injection\Behavior\Registry::resolveModeBasket();

		if (isset($resolveModeElement[$this->mode]))
		{
			$productId = $this->productId;
			$offerId = $state->environment->getProduct()->resolveOffer($productId);

			$state->order->initEmptyBasket();

			$basketData = $this->getProductBasketData($state, $offerId);
			$quantity = $basketData['RATIO'] ?? 1;
			$addResult = $state->order->addProduct($offerId, $quantity, $basketData);
		}
		elseif (isset($resolveModeBasket[$this->mode]))
		{
			$addResult = $state->order->initUserBasket();
		}
		else
		{
			throw new Reference\Exceptions\DtoProperty('not found mode');
		}

		Exceptions\Facade::handleResult($addResult, Reference\Exceptions\DtoProperty::class);
	}

	protected function getProductBasketData(State\OrderCalculation $state, int $productId) : array
	{
		$environmentProduct = $state->environment->getProduct();
		$fewData = $environmentProduct->getBasketData([$productId]);
		$itemData = $fewData[$productId] ?? null;

		Assert::notNull($itemData, '$enviromentProduct->getBasketData()');

		if (isset($itemData['ERROR']))
		{
			throw new Reference\Exceptions\DtoProperty($itemData['ERROR']);
		}

		return $itemData;
	}
}