<?php
namespace YandexPay\Pay\Trading\Action\Rest\State;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity as TradingEntity;

class Order extends Common
{
	/** @var TradingEntity\Reference\Order */
	public $orderAdapter;
	/** @var Sale\Order */
	public $order;
	/** @var Sale\Payment */
	public $payment;
	/** @var Sale\Basket */
	public $basket;
	/** @var Sale\Delivery\Services\Base */
	public $delivery;
	/** @var \Sale\Handlers\PaySystem\YandexPayHandler */
	public $handler;
}