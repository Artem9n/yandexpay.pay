<?php

namespace YandexPay\Pay\Components;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PaySystem;
use Sale\Handlers\PaySystem\YandexPayHandler;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Utils\JsonBodyFilter;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); };

Loc::loadMessages(__FILE__);

class TradingCart extends \CBitrixComponent
{
	/** @var PaySystem\BaseServiceHandler */
	protected $handler;
	/** @var PaySystem\Service */
	protected $service;

	public function onPrepareComponentParams($arParams) : array
	{
		$arParams['PRODUCT_ID'] = !empty($arParams['PRODUCT_ID']) ? (int)$arParams['PRODUCT_ID'] : null;
		$arParams['PAY_SYSTEM_ID'] = !empty($arParams['PAY_SYSTEM_ID']) ? (int)$arParams['PAY_SYSTEM_ID'] : null;

		return $arParams;
	}

	public function executeComponent(): void
	{
		try
		{

			$this->loadModules();
			$handler = $this->getHandler();

			pr($handler->getParamsBusValue());
		}
		catch (Main\SystemException $exception)
		{
			pr($exception->getMessage());
		}
	}

	protected function getHandler() : ?PaySystem\BaseServiceHandler
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

		[$className, $handlerType] = PaySystem\Manager::includeHandler($service->getField('ACTION_FILE'));

		$this->handler = new $className($handlerType, $service);

		Assert::typeOf($this->handler, YandexPayHandler::class, 'handler');

		return $this->handler;
	}

	protected function getService() : PaySystem\Service
	{
		if ($this->service === null)
		{
			$this->service = $this->loadService();
		}

		return $this->service;
	}

	protected function loadService() : ?PaySystem\Service
	{
		if ($this->arParams['PAY_SYSTEM_ID'] === null || $this->arParams['PAY_SYSTEM_ID'] <= 0 )
		{
			throw new Main\SystemException('not fill pay system id');
		}

		$result = null;

		$query = PaySystem\Manager::getList([
			'filter' => [
				'=ID' => $this->arParams['PAY_SYSTEM_ID'],
				'=ACTION_FILE' => 'yandexpay',
				'ACTIVE' => 'Y'
			],
			'select' => ['*']
		]);

		if ($item = $query->fetch())
		{
			$result = new PaySystem\Service($item);
		}

		return $result;
	}

	protected function parseRequest() : void
	{
		$this->request->addFilter(new JsonBodyFilter());
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

	protected function getLang(string $code, $replace = null, $language = null): string
	{
		return Main\Localization\Loc::getMessage('YANDEX_PAY_TRADING_CART_' . $code, $replace, $language);
	}
}