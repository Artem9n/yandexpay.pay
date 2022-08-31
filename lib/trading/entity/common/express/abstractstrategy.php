<?php
namespace YandexPay\Pay\Trading\Entity\Common\Express;

use YandexPay\Pay\Trading\Action\Rest\Dto;

abstract class AbstractStrategy
{
	abstract public function title() : string;

	abstract public function resolve(array $storeIds, Dto\Address $address = null, array $context = []) : ?int;
}