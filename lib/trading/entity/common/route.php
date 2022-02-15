<?php

namespace YandexPay\Pay\Trading\Entity\Common;

use Bitrix\Main;
use YandexPay\Pay;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;

class Route extends EntityReference\Route
{
	public function getPublicPath(string $urlId) : string
	{
		return $this->getBasePath() . '/' . $urlId;
	}

	public function installPublic(string $siteId) : void
	{
		$rule = $this->getUrlRewriteRule();

		Main\UrlRewriter::add($siteId, $rule);
	}

	public function uninstallPublic(string $siteId) : void
	{
		$rule = $this->getUrlRewriteRule();
		unset($rule['RULE']);

		Main\UrlRewriter::delete($siteId, $rule);
	}

	protected function getUrlRewriteRule() : array
	{
		$path = $this->getBasePath();

		return [
			'CONDITION' => '#^' . $path . '/#',
			'RULE' => '',
			'ID' => '',
			'PATH' => $path . '/index.php',
		];
	}

	protected function getBasePath() : string
	{
		$moduleName = Pay\Config::getModuleName();

		return BX_ROOT . '/services/' . $moduleName . '/trading';
	}
}