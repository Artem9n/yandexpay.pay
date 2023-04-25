<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderRender\Stage;

use Sale\Handlers;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Action\Rest\Stage\ResponseCollector;

class DiscountsCollector extends ResponseCollector
{
	use Concerns\HasMessage;

	public function __invoke(State\Order $state) : void
	{
		$result = [];

		$discountIds = [];

		$discountAmount = $this->sumPaid($state);

		if ($discountAmount > 0)
		{
			$discountIds[] = 'paid';
		}

		/** @var \Bitrix\Sale\BasketItem $basketItem */
		foreach ($state->order->getBasket() as $basketItem)
		{
			$discountAmount += $basketItem->getDiscountPrice() * $basketItem->getQuantity();
		}

		if ($discountAmount <= 0) { return; }

		$discountList = $state->order->getDiscount()->getApplyResult();
		$discountIds = array_merge(array_column($discountList['DISCOUNT_LIST'], 'REAL_DISCOUNT_ID'), $discountIds);

		$result[] = [
			'amount' => (string)$discountAmount,
			'description' => self::getMessage('DISCOUNT_DESCRIPTION'),
			'discountId' => implode(':', $discountIds),
		];

		$this->write($result, 'cart.discounts');
	}

	protected function sumPaid(State\Order $state) : float
	{
		$result = 0;

		/** @var \Bitrix\Sale\Payment $payment */
		foreach ($state->order->getPaymentCollection() as $payment)
		{
			$handler = $state->environment->getPaySystem()->getHandler($payment->getPaymentSystemId());

			if ($handler instanceof Handlers\PaySystem\YandexPayHandler) { continue; }

			$result += $payment->getSum();
		}

		return $result;
	}
}

