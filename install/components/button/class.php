<?php

namespace YandexPay\Pay\Components;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Main\Localization\Loc;
use YandexPay\Pay\Gateway\Manager;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading;
use YandexPay\Pay\Injection;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;
use YandexPay\Pay\Trading\Entity\Registry as EntityRegistry;
use YandexPay\Pay\Utils;
use Sale\Handlers\PaySystem\YandexPayHandler;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

Loc::loadMessages(__FILE__);

class TradingButton extends \CBitrixComponent
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
		$arParams['VARIANT_BUTTON'] = !empty($arParams['VARIANT_BUTTON']) ? (string)$arParams['VARIANT_BUTTON'] : null;
		$arParams['WIDTH_BUTTON'] = !empty($arParams['WIDTH_BUTTON']) ? (string)$arParams['WIDTH_BUTTON'] : null;
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
			$this->includeComponentTemplate();
		}
		catch (Main\SystemException $exception)
		{
			// todo  loggers

			/*echo '<pre>';
			print_r($exception->getMessage());
			echo '</pre>';*/
		}
	}

	protected function setParameters() : void
	{
		$setup = $this->getSetup();

		$setup->wakeupOptions();

		$options = $setup->getOptions();

		$paySystemId = $options->getPaymentCard();

		$handler = $this->getHandler($paySystemId);

		$params = $handler->getParamsBusValue(); // todo make dummy payment with PERSON_TYPE

		$gateway = $handler->getGateway();
		$gateway->setParameters($params);

		$this->arResult['PARAMS'] = [
			'env'               => $handler->isTestMode() ? 'SANDBOX' : 'PRODUCTION',
			'merchantId'        => $params['YANDEX_PAY_MERCHANT_ID'],
			'merchantName'      => $params['YANDEX_PAY_MERCHANT_NAME'],
			'buttonTheme'       => $this->arParams['VARIANT_BUTTON'],
			'buttonWidth'       => $this->arParams['WIDTH_BUTTON'],
			'gateway'           => $gateway->getGatewayId(),
			'gatewayMerchantId' => $gateway->getMerchantId(),
			'useEmail'          => $options->useBuyerEmail(),
			'useName'           => $options->useBuyerName(),
			'usePhone'          => $options->useBuyerPhone(),
			'purchaseUrl'       => $options->getPurchaseUrl(),
			'notifyUrl'         => $params['YANDEX_PAY_NOTIFY_URL'],
			'siteUrl'           => Utils\Url::absolutizePath(),
			'productId'         => $this->arParams['PRODUCT_ID'],
			'siteId'            => $setup->getSiteId(),
			'setupId'           => $setup->getId(),
			'paySystemId'       => $options->getPaymentCard(),
			'paymentCash'       => $options->getPaymentCash(),
			'mode'              => $this->arParams['MODE'],
			'selector'          => $this->arParams['SELECTOR'],
			'position'          => $this->arParams['POSITION'],
			'solution'          => $options->getSolution(),
		];
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
			],
			'limit' => 1,
		])->fetchObject();

		Assert::notNull($result, 'setup');

		$result->fill();

		return $result;
	}

	protected function getHandler(int $paySystemId) : YandexPayHandler
	{
		/** @var YandexPayHandler $result */
		$result = $this->environment->getPaySystem()->getHandler($paySystemId);

		Assert::typeOf($result, YandexPayHandler::class, 'handler');

		return $result;
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
		return Main\Localization\Loc::getMessage('YANDEX_PAY_BUTTON_' . $code, $replace, $language);
	}
}