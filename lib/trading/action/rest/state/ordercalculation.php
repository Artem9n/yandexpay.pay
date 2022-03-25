<?php
namespace YandexPay\Pay\Trading\Action\Rest\State;

use YandexPay\Pay\Trading\Entity as TradingEntity;

class OrderCalculation extends Common
{
	/** @var TradingEntity\Reference\Order */
	public $order;
	/** @var int|null */
	public $userId;
	/** @var int|null */
	public $fUserId;
	/** @var array<int, string> $productIndex => $basketCode  */
	public $basketMap = [];
}

