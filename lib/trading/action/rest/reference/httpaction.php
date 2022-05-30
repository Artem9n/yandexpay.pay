<?php
namespace YandexPay\Pay\Trading\Action\Rest\Reference;

use Bitrix\Main;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Entity as TradingEntity;
use YandexPay\Pay\Trading\Setup as TradingSetup;
use YandexPay\Pay\Trading\Settings as TradingSettings;

abstract class HttpAction
{
	/** @var Main\HttpRequest*/
	protected $httpRequest;
	/** @var Main\Server */
	protected $httpServer;
	/** @var TradingSetup\Model */
	protected $setup;
	/** @var TradingSettings\Options */
	protected $options;
	/** @var TradingEntity\Reference\Environment */
	protected $environment;
	/** @var bool */
	protected $isTestMode = false;

	public function passTrading(TradingSetup\Model $setup, bool $isTestMode = false) : void
	{
		$this->setup = $setup;
		$this->options = $this->setup->wakeupOptions();
		$this->isTestMode = $isTestMode;
	}

	public function passHttp(Main\HttpRequest $request = null, Main\Server $server = null) : void
	{
		$this->httpRequest = $request ?? Main\Context::getCurrent()->getRequest();
		$this->httpServer = $server ?? Main\Context::getCurrent()->getServer();
	}

	public function bootstrap() : void
	{
		// nothing by default
	}

	abstract public function process() : Main\HttpResponse;

	protected function getHttpRequest() : Main\HttpRequest
	{
		Assert::notNull($this->httpRequest, 'httpRequest');

		return $this->httpRequest;
	}

	protected function getHttpServer() : Main\Server
	{
		Assert::notNull($this->httpServer, 'httpServer');

		return $this->httpServer;
	}
}