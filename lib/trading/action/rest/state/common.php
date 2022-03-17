<?php
namespace YandexPay\Pay\Trading\Action\Rest\State;

use YandexPay\Pay\Trading\Setup as TradingSetup;
use YandexPay\Pay\Trading\Entity as TradingEntity;
use YandexPay\Pay\Trading\Settings as TradingSettings;

class Common
{
	/** @var TradingSetup\Model */
	public $setup;
	/** @var TradingSettings\Options */
	public $options;
	/** @var TradingEntity\Reference\Environment */
	public $environment;
	/** @var bool */
	public $isTestMode;
}

