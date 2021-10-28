<?php

namespace YandexPay\Pay\Components;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Main\Localization\Loc;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Setup;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;
use YandexPay\Pay\Trading\Entity\Registry as EntityRegistry;
use YandexPay\Pay\Utils;
use Bitrix\Sale\PaySystem;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

Loc::loadMessages(__FILE__);

class TradingCart extends \CBitrixComponent
{
	/** @var Sale\PaySystem\BaseServiceHandler */
	protected $handler;
	/** @var Sale\PaySystem\Service */
	protected $service;
	/** @var EntityReference\Environment */
	protected $environment;
	/** @var Setup\Model */
	protected $setup;

	public function onPrepareComponentParams($arParams) : array
	{
		$arParams['PRODUCT_ID'] = !empty($arParams['PRODUCT_ID']) ? (int)$arParams['PRODUCT_ID'] : null;
		$arParams['PAY_SYSTEM_ID'] = !empty($arParams['PAY_SYSTEM_ID']) && (int)$arParams['PAY_SYSTEM_ID'] > 0 ? (int)$arParams['PAY_SYSTEM_ID'] : null;
		$arParams['SETUP_ID'] = !empty($arParams['SETUP_ID']) ? (int)$arParams['SETUP_ID'] : null;
		$arParams['MODE'] = !empty($arParams['MODE']) ? (string)$arParams['MODE'] : 'PRODUCT';

		return $arParams;
	}

	public function executeComponent(): void
	{
		try
		{
			$this->loadModules();
			$this->bootstrap();

			$handler = $this->getHandler();
			Assert::notNull($handler, 'handler', $this->getLang('NOT_LOAD_HANDLER'));

			$this->setParameters($handler);
			$this->setRedirectUrl(); // todo ��������� ������� ��������� backurl, ���� ����� ���������

			$this->includeComponentTemplate();
		}
		catch (Main\SystemException $exception)
		{
			pr($exception->getMessage());
		}
	}

	protected function setParameters(Sale\PaySystem\BaseServiceHandler $handler) : void
	{
		global $USER;

		$params = $handler->getParamsBusValue();
		$cardNetworcks = $handler->getCardNetworks();

		$setup = $this->getSetup();
		$setup->wakeupOptions();
		$setup->fillSiteId();
		$options = $setup->getOptions();

		$gateway = $this->service->getField('PS_MODE');

		$this->arResult['PARAMS'] = [
			'env'               => $params['YANDEX_PAY_TEST_MODE'] === 'Y' ? 'SANDBOX' : 'PRODUCTION',
			'merchantId'        => $params['YANDEX_PAY_MERCHANT_ID'],
			'merchantName'      => $params['YANDEX_PAY_MERCHANT_NAME'],
			'buttonTheme'       => $params['YANDEX_PAY_VARIANT_BUTTON'],
			'buttonWidth'       => $params['YANDEX_PAY_WIDTH_BUTTON'],
			'cardNetworks'      => $cardNetworcks,
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
			'paySystemId'       => $this->arParams['PAY_SYSTEM_ID'],
			'mode'              => $this->arParams['MODE'],
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
		$_SESSION['yabackurl'] = $url;
	}

	protected function getSetup() : Setup\Model
	{
		if ($this->setup === null)
		{
			$this->setup = $this->loadSetup();
		}

		return $this->setup;
	}

	protected function loadSetup() : Setup\Model
	{
		return Setup\Model::wakeUp(['ID' => $this->arParams['SETUP_ID']]);
	}

	protected function getHandler() : ?Sale\PaySystem\BaseServiceHandler
	{
		if ($this->handler === null)
		{
			$this->handler = $this->loadHandler();
		}

		return $this->handler;
	}

	protected function loadHandler()
	{
		$service = $this->getService();
		Assert::notNull($service, 'service', $this->getLang('NOT_LOAD_SERVICE'));

		$actionFile = $service->getField('ACTION_FILE');

		if (method_exists(Sale\PaySystem\Manager::class, 'includeHandler'))
		{
			[$className, $handlerType] = Sale\PaySystem\Manager::includeHandler($actionFile);
		}
		else
		{
			[$className, $handlerType] = self::includeHandler($actionFile);
		}

		$this->handler = new $className($handlerType, $service);

		Assert::typeOf($this->handler, \Sale\Handlers\PaySystem\YandexPayHandler::class, 'handler');

		return $this->handler;
	}

	protected static function includeHandler(string $actionFile) : array
	{
		$handlerType = '';
		$className = '';

		$name = Sale\PaySystem\Manager::getFolderFromClassName($actionFile);

		foreach (Sale\PaySystem\Manager::getHandlerDirectories() as $type => $path)
		{
			$documentRoot = Main\Application::getDocumentRoot();
			if (Main\IO\File::isFileExists($documentRoot.$path.$name.'/handler.php'))
			{
				$className = Sale\PaySystem\Manager::getClassNameFromPath($actionFile);
				if (!class_exists($className))
					require_once($documentRoot.$path.$name.'/handler.php');

				if (class_exists($className))
				{
					$handlerType = $type;
					break;
				}

				$className = '';
			}
		}

		if ($className === '')
		{
			if (Sale\PaySystem\Manager::isRestHandler($actionFile))
			{
				$className = PaySystem\RestHandler::class;
				if (!class_exists($actionFile))
				{
					class_alias($className, $actionFile);
				}
			}
			else
			{
				$className = PaySystem\CompatibilityHandler::class;
			}
		}

		return [
			$className,
			$handlerType,
		];
	}

	protected function getService() : ?Sale\PaySystem\Service
	{
		if ($this->service === null)
		{
			$this->service = $this->loadService();
		}

		return $this->service;
	}

	protected function loadService() : ?Sale\PaySystem\Service
	{
		Assert::notNull($this->arParams['PAY_SYSTEM_ID'], 'paySystem id', $this->getLang('NOT_PAY_SYSTEM_ID'));

		$result = null;

		$query = Sale\PaySystem\Manager::getList([
			'filter' => [
				'=ID' => $this->arParams['PAY_SYSTEM_ID'],
				'=ACTION_FILE' => 'yandexpay',
				'ACTIVE' => 'Y'
			],
			'select' => ['*']
		]);

		if ($item = $query->fetch())
		{
			$result = new Sale\PaySystem\Service($item);
		}

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
		return Main\Localization\Loc::getMessage('YANDEX_PAY_TRADING_CART_' . $code, $replace, $language);
	}
}