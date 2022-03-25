<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderRender;

use YandexPay\Pay\Trading\Action;
use YandexPay\Pay\Trading\Action\Rest;

class Request extends Rest\Reference\EffectiveRequest
{
	public function getMetadata() : array
	{
		return unserialize($this->getField('metadata'), [false]);
	}

	public function getUserId() : ?int
	{
		$data = $this->getMetadata();

		return $data['userId'];
	}

	public function getFUserId() : ?int
	{
		$data = $this->getMetadata();

		return $data['fUserId'];
	}

	public function getCurrencyCode()
	{
		return $this->requireField('currencyCode');
	}

	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getItems() : Rest\Dto\Cart\Items
	{
		return $this->getChildCollection('cart.items');
	}

	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getAddress() : ?Rest\Dto\Address
	{
		return $this->getChildModel('shippingAddress');
	}

	public function getCoupons() : ?array
	{
		return $this->getField('cart.coupons');
	}

	protected function collectionMap() : array
	{
		return [
			'cart.items' => Rest\Dto\Cart\Items::class,
		];
	}

	protected function modelMap() : array
	{
		return [
			'shippingAddress' => Rest\Dto\Address::class,
		];
	}
}