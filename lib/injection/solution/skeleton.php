<?php
namespace YandexPay\Pay\Injection\Solution;

use Bitrix\Main;

abstract class Skeleton
{
	abstract public function getTitle() : string;

	abstract public function getType() : string;

	abstract public function isMatch(array $context = []) : bool;

	abstract public function getDefaults(array $context = []) : array;

	abstract public function getOrderPath(array $context = []) : string;

	public function getAssets() : ?array
	{
		$type = mb_strtolower($this->getType());

		return Main\UI\Extension::getAssets('yandexpaypay.solution.' . $type);
	}

	/** @deprecated */
	public function getExtension() : ?string
	{
		$type = mb_strtolower($this->getType());

		return Main\UI\Extension::getHtml('yandexpaypay.solution.' . $type);
	}
}