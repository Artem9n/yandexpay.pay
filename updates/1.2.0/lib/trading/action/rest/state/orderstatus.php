<?php
namespace YandexPay\Pay\Trading\Action\Rest\State;

class OrderStatus extends Common
{
	/** @var \Bitrix\Sale\Order */
	public $order;
	/** @var \Bitrix\Sale\Payment */
	public $payment;
	/** @var \Sale\Handlers\PaySystem\YandexPayHandler */
	public $handler;
	/** @var string */
	public $status;
}