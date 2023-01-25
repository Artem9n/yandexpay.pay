<?php

namespace YandexPay\Pay\Delivery\Yandex\Api\Cancel;

use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action;
use YandexPay\Pay\Delivery\Yandex\Api;

class Request extends Api\Reference\Request
{
	/** @var string */
	protected $cancelState;

	public function getPath() : string
	{
		return sprintf('/api/merchant/v1/orders/%s/delivery/cancel', $this->getOrderNumber());
	}

	public function getQuery() : array
	{
		return [
			'cancelState' => $this->getCancelState(),
		];
	}

	public function setCancelState(string $cancelString) : void
	{
		$this->cancelState = $cancelString;
	}

	protected function getCancelState() : string
	{
		Assert::notNull($this->cancelState, 'cancelState','cancelState not set', Action\Reference\Exceptions\DtoProperty::class);

		return $this->cancelState;
	}
}