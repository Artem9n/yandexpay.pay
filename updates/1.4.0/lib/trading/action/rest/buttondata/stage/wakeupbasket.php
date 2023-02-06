<?php
namespace YandexPay\Pay\Trading\Action\Rest\ButtonData\Stage;

use Bitrix\Main;
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

		$this->handleResultAddBasket($addResult);
	}

	protected function handleResultAddBasket(Main\Result $result) : void
	{
		if ($result->isSuccess()) { return; }

		$exceptionClass = Reference\Exceptions\DtoProperty::class;

		foreach($result->getErrors() as $error)
		{
			if($error->getCode() === 'CATALOG_QUANTITY_NOT_ENOGH' //You want to buy #QUANTITY# #MEASURE_NAME#. of #NAME#; however, only #CATALOG_QUANTITY# #MEASURE_NAME#. are available.
				|| $error->getCode() === 'CATALOG_NO_QUANTITY_PRODUCT' //#NAME# is out of stock.
				|| $error->getCode() === 'SALE_BASKET_AVAILABLE_QUANTITY' //Sorry, the product quantity you selected is currently unavailable.<br>Using the previous correct value.
				|| $error->getCode() === 'SALE_BASKET_ITEM_WRONG_AVAILABLE_QUANTITY' //Error checking availability of \"#PRODUCT_NAME#\"
				|| $error->getCode() === 'EMPTY_BASKET' //Empty basket
			)
			{
				$exceptionClass = Reference\Exceptions\AvailableProduct::class; //not log error
				break;
			}
		}

		Exceptions\Facade::handleResult($result, $exceptionClass);
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