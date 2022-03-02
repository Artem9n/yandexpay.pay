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

	public function getExtension() : ?string
	{
		$name = $this->getExtensionName();

		return Main\UI\Extension::getHtml($name);
	}

	public function getExtensionFiles() : array
	{
		$name = $this->getExtensionName();

		return Main\UI\Extension::getAssets($name);
	}

	protected function getExtensionName() : string
	{
		$type = mb_strtolower($this->getType());

		return 'yandexpaypay.solution.' . $type;
	}
}