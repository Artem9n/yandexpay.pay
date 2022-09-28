<?php
namespace YandexPay\Pay\Trading\Action\Rest\Reference;

use Bitrix\Main;
use YandexPay\Pay\Logger;
use YandexPay\Pay\Psr;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Entity as TradingEntity;
use YandexPay\Pay\Trading\Setup as TradingSetup;
use YandexPay\Pay\Trading\Settings as TradingSettings;

abstract class HttpAction implements Psr\Log\LoggerAwareInterface
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
	/** @var Psr\Log\LoggerInterface */
	protected $logger;

	public function passTrading(TradingSetup\Model $setup, bool $isTestMode = false) : void
	{
		$this->setup = $setup;
		$this->options = $this->setup->wakeupOptions();
		$this->isTestMode = $isTestMode;

		$this->configureLogger();
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

	public function setLogger(Psr\Log\LoggerInterface $logger) : void
	{
		$this->logger = $logger;
		$this->configureLogger();
	}

	protected function configureLogger() : void
	{
		if ($this->logger instanceof Logger\Logger && $this->setup !== null)
		{
			$this->logger->setSetup($this->setup);
			$this->logger->setLevel($this->setup->getOptions()->getLogLevel());
		}
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