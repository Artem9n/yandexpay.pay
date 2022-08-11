<?php

namespace YandexPay\Pay\Trading\Entity\Sale;

use Bitrix\Sale;
use YandexPay\Pay;
use YandexPay\Pay\Reference\Concerns;

class Platform extends Pay\Trading\Entity\Reference\Platform
{
	use Concerns\HasMessage;

	public const TRADING_PLATFORM_CODE = 'yapay_checkout';

	protected $systemPlatform;

	public function getId() : int
	{
		return $this->getSystemPlatform()->getId();
	}

	public function install() : void
	{
		if ($this->getSystemPlatform()->isInstalled()) { return; }

		$this->getSystemPlatform()->install();
	}

	public function uninstall() : void
	{
		$this->getSystemPlatform()->uninstall();
	}

	protected function getSystemPlatform() : Sale\TradingPlatform\Platform
	{
		if ($this->systemPlatform === null)
		{
			$this->systemPlatform = $this->loadSystemPlatform();
		}

		return $this->systemPlatform;
	}

	public function activate() : void
	{
		$this->getSystemPlatform()->setActive();
	}

	public function deactivate() : void
	{
		$this->getSystemPlatform()->unsetActive();
	}

	public function getSalePlatform() : Sale\TradingPlatform\Platform
	{
		return $this->getSystemPlatform();
	}

	/**
	 * @return \Bitrix\Sale\TradingPlatform\Platform
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	protected function loadSystemPlatform() : Sale\TradingPlatform\Platform
	{
		return Pay\Trading\Entity\Sale\Internals\Platform::getInstanceByCode(static::TRADING_PLATFORM_CODE);
	}
}