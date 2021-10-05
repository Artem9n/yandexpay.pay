<?php

namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Sale;
use Bitrix\Main;

abstract class Order
{
	/** @var Environment */
	protected $environment;
	/** @var Sale\OrderBase */
	protected $internalOrder;

	public function __construct(Environment $environment, $internalOrder)
	{
		$this->environment = $environment;
		$this->internalOrder = $internalOrder;
	}

	/**
	 * @param int|string $productId
	 * @param int        $count
	 * @param array|null $data
	 *
	 * @return Main\Result
	 */
	public function addProduct($productId, int $count = 1, array $data = null) : Main\Result
	{
		throw new Main\NotImplementedException('addProduct is missing');
	}
}