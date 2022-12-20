<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderRender\Stage;

use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Action\Rest\Stage\ResponseCollector;

class OptionsCollector extends ResponseCollector
{
	/** @var array */
	protected $items = [];

	public function __invoke(State\Order $state)
	{
		$this->write($state->order->getCurrency(), 'currencyCode');

		$this->write(['CARD', 'CASH_ON_DELIVERY', 'SPLIT'], 'availablePaymentMethods');
		$this->write(false, 'enableCoupons');
		$this->write(false, 'enableCommentField');

		$result = [
			'billingContact' => [
				'name' => true,
				'email' => true,
				'phone' => true,
			],
			'shippingContact' => [
				'name' => true,
				'email' => true,
				'phone' => true,
			],
		];

		$this->write($result, 'requiredFields');
	}
}

