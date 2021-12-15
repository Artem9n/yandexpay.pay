<?php

namespace YandexPay\Pay\Components;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Main\Localization\Loc;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading;
use YandexPay\Pay\Injection;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;
use YandexPay\Pay\Trading\Entity\Registry as EntityRegistry;
use YandexPay\Pay\Utils;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

Loc::loadMessages(__FILE__);

class TradingCart extends \CBitrixComponent
{
	/** @var \Sale\Handlers\PaySystem\YandexPayHandler */
	protected $handler;
	/** @var Sale\PaySystem\Service */
	protected $service;
	/** @var EntityReference\Environment */
	protected $environment;
	/** @var Trading\Setup\Model */
	protected $setup;
	/** @var Injection\Setup\Model */
	protected $injection;

	public function onPrepareComponentParams($arParams) : array
	{
		$arParams['PRODUCT_ID'] = !empty($arParams['PRODUCT_ID']) ? (int)$arParams['PRODUCT_ID'] : null;
		$arParams['TRADING_ID'] = !empty($arParams['TRADING_ID']) ? (int)$arParams['TRADING_ID'] : null;
		$arParams['POSITION'] = !empty($arParams['POSITION']) ? (string)$arParams['POSITION'] : null;
		$arParams['SELECTOR'] = !empty($arParams['SELECTOR']) ? (string)$arParams['SELECTOR'] : null;
		$arParams['MODE'] = !empty($arParams['MODE']) ? (string)$arParams['MODE'] : null;

		return $arParams;
	}

	public function executeComponent(): void
	{
		try
		{
			$this->loadModules();
			$this->bootstrap();

			$this->setParameters();
			$this->setRedirectUrl(); // todo временное решение установки backurl, надо будет пофиксить

			$this->includeComponentTemplate();
		}
		catch (Main\SystemException $exception)
		{
			echo '<pre>';
			print_r($exception->getMessage());
			echo '</pre>';
		}
	}

	protected function setParameters() : void
	{
		global $USER;

		$setup = $this->getSetup();

		$setup->wakeupOptions();

		$options = $setup->getOptions();

		$paySystemId = $options->getPaymentCard();

		$this->getHandler($paySystemId);

		$params = $this->handler->getParamsBusValue();
		$cardNetworks = $this->getCardNetworks();
		$gateway = $this->handler->getHandlerMode();

		$this->arResult['PARAMS'] = [
			'env'               => $this->handler->isTestMode() ? 'SANDBOX' : 'PRODUCTION',
			'merchantId'        => $params['YANDEX_PAY_MERCHANT_ID'],
			'merchantName'      => $params['YANDEX_PAY_MERCHANT_NAME'],
			'buttonTheme'       => $params['YANDEX_PAY_VARIANT_BUTTON'],
			'buttonWidth'       => $params['YANDEX_PAY_WIDTH_BUTTON'],
			'cardNetworks'      => $cardNetworks,
			'gateway'           => $gateway,
			'gatewayMerchantId' => $params['YANDEX_PAY_' . $gateway . '_PAYMENT_GATEWAY_MERCHANT_ID'],
			'useEmail'          => (bool)$options->getValue('USE_BUYER_EMAIL'),
			'useName'           => (bool)$options->getValue('USE_BUYER_NAME'),
			'usePhone'          => (bool)$options->getValue('USE_BUYER_PHONE'),
			'purchaseUrl'       => $options::getPurchaseUrl(),
			'notifyUrl'         => $params['YANDEX_PAY_NOTIFY_URL'],
			'siteUrl'           => Utils\Url::absolutizePath(),
			'productId'         => $this->arParams['PRODUCT_ID'],
			'siteId'            => $setup->getSiteId(),
			'userId'            => $USER->GetID(),
			'setupId'           => $setup->getId(),
			'fUserId'           => Sale\Fuser::getId(true),
			'paySystemId'       => $options->getPaymentCard(),
			'paymentCash'       => $options->getPaymentCash(),
			'mode'              => $this->arParams['MODE'],
			'selector'          => $this->arParams['SELECTOR'],
			'position'          => $this->arParams['POSITION'],
			'order'             => [
				'id' => '0',
				'total' => '0'
			]
		];
	}

	protected function setRedirectUrl() : void
	{
		global $APPLICATION;

		$server = Main\Context::getCurrent()->getServer();
		$request = Main\Context::getCurrent()->getRequest();
		$host = $request->isHttps() ? 'https' : 'http';
		$url = $host . '://' . $server->get('SERVER_NAME') . $APPLICATION->GetCurPage();
		$_SESSION['yabehaviorbackurl'] = $url;
	}

	public function getCardNetworks() : array
	{
		$result = [];

		$parameters = $this->handler->getParamsBusValue();
		$str = 'YANDEX_CARD_NETWORK_';
		$strLength = mb_strlen($str);

		foreach ($parameters as $code => $value)
		{
			$position = mb_strpos($code, $str);

			if ($position !== false && $value === 'Y')
			{
				$cardName = mb_substr($code, $strLength);
				$result[] = $cardName;
			}
		}

		return $result;
	}

	protected function getSetup() : Trading\Setup\Model
	{
		if ($this->setup === null)
		{
			$this->setup = $this->loadSetup();
		}

		return $this->setup;
	}

	protected function loadSetup() : Trading\Setup\Model
	{
		$result = Trading\Setup\RepositoryTable::getList([
			'filter' => [
				'ID' => $this->arParams['TRADING_ID'],
				'ACTIVE' => true
			]
		])->fetchObject();

		Assert::notNull($result, 'setup');

		$result->fill();

		return $result;
	}

	protected function getHandler(int $paySystemId) : Sale\PaySystem\BaseServiceHandler
	{
		$this->handler = $this->environment->getPaySystem()->getHandler($paySystemId);

		Assert::typeOf($this->handler, \Sale\Handlers\PaySystem\YandexPayHandler::class, 'handler');

		return $this->handler;
	}

	protected function loadModules(): void
	{
		$requiredModules = $this->getRequiredModules();

		foreach ($requiredModules as $requiredModule)
		{
			if (!Main\Loader::includeModule($requiredModule))
			{
				$message = $this->getLang('MODULE_NOT_INSTALLED', [ '#MODULE_ID#' => $requiredModule ]);

				throw new Main\SystemException($message);
			}
		}
	}

	protected function getRequiredModules(): array
	{
		return [
			'yandexpay.pay',
			'sale'
		];
	}

	protected function bootstrap() : void
	{
		$this->environment = EntityRegistry::getEnvironment();
	}

	protected function getLang(string $code, $replace = null, $language = null): string
	{
		return Main\Localization\Loc::getMessage('YANDEX_PAY_TRADING_CART_' . $code, $replace, $language);
	}
}