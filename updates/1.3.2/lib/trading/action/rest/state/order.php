<?php
namespace YandexPay\Pay\Trading\Action\Rest\State;

use Bitrix\Sale;

class Order extends Common
{
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