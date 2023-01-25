<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate;

use YandexPay\Pay\Trading\Action;
use YandexPay\Pay\Trading\Action\Rest;

class Request extends Rest\Reference\EffectiveRequest
{
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

	public function getOrderId() : ?string
	{
		return $this->getField('orderId');
	}

	/**
	 * enum<COURIER|PICKUP|YANDEX_DELIVERY>
	 * @return string
	 */
	public function getDeliveryType() : ?string
	{
		return $this->getField('shippingMethod.methodType');
	}

	public function getPaymentType() : string
	{
		return $this->requireField('paymentMethod.methodType');
	}

	public function getCoupons() : ?array
	{
		return $this->getField('cart.coupons');
	}

	public function getOrderAmount() : float
	{
		return $this->requireField('orderAmount');
	}

	public function getCartId() : string
	{
		return $this->requireField('cart.cartId');
	}

	/**
	 * @noinspection PhpIncompatibleReturnTypeInspection
	 */
	public function getDelivery() : ?Rest\OrderCreate\Dto\Delivery
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

	/**
	 * @noinspection PhpIncompatibleReturnTypeInspection
	 */
	public function getYandexDelivery() : Rest\OrderCreate\Dto\YandexDelivery
	{
		return $this->getChildModel('shippingMethod.yandexDeliveryOption');
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
			'shippingMethod.yandexDeliveryOption' => Rest\OrderCreate\Dto\YandexDelivery::class,
		];
	}
}