<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate;

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

	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getUser() : Rest\OrderCreate\Dto\User
	{
		return $this->getChildModel('shippingContact');
	}

	/**
	 * enum<COURIER|PICKUP|YANDEX_DELIVERY>
	 * @return string
	 */
	public function getDeliveryType() : string
	{
		return $this->requireField('shippingMethod.methodType');
	}

	public function getPaymentType() : string
	{
		return $this->requireField('paymentMethod.methodType');
	}

	public function getCoupons() : ?array
	{
		return $this->getField('cart.coupons');
	}

	public function getComment() : ?string
	{
		return $this->getField('cart.comment');
	}

	public function getOrderAmount() : float
	{
		return $this->requireField('orderAmount');
	}

	/**
	 * @noinspection PhpIncompatibleReturnTypeInspection
	 */
	public function getDelivery() : Rest\OrderCreate\Dto\Delivery
	{
		return $this->getChildModel('shippingMethod.courierOption');
	}

	/**
	 * @noinspection PhpIncompatibleReturnTypeInspection
	 */
	public function getPickup() : Rest\OrderCreate\Dto\Pickup
	{
		return $this->getChildModel('shippingMethod.pickupOption');
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
			'shippingContact' => Rest\OrderCreate\Dto\User::class,
			'shippingMethod.courierOption' => Rest\OrderCreate\Dto\Delivery::class,
			'shippingMethod.pickupOption' => Rest\OrderCreate\Dto\Pickup::class,
		];
	}
}