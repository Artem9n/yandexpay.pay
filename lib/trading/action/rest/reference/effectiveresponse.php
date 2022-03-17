<?php
namespace YandexPay\Pay\Trading\Action\Rest\Reference;

use YandexPay\Pay\Trading\Action\Reference\Dto;
use YandexPay\Pay\Utils;

class EffectiveResponse extends Dto
{
	public function setField($name, $value) : void
	{
		Utils\DotChain::set($this->fields, $name, $value);
	}
}