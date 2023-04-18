<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderRender\Stage;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Action\Rest\Stage\ResponseCollector;

class DiscountsCollector extends ResponseCollector
{
	use Concerns\HasMessage;

	public function __invoke(State\Order $state) : void
	{
		$result = [];

		$discountList = $state->order->getDiscount()->getApplyResult();
		$discountIds = array_column($discountList['DISCOUNT_LIST'], 'REAL_DISCOUNT_ID');
		$discountAmount = 0;

		/** @var \Bitrix\Sale\BasketItem $basketItem */
		foreach ($state->order->getBasket() as $basketItem)
		{
			$discountAmount += $basketItem->getDiscountPrice() * $basketItem->getQuantity();
		}

		if ($discountAmount <= 0) { return; }

		$result[] = [
			'amount' => (string)$discountAmount,
			'description' => self::getMessage('DISCOUNT_DESCRIPTION'),
			'discountId' => implode(':', $discountIds),
		];

		$this->write($result, 'cart.discounts');
	}
}

