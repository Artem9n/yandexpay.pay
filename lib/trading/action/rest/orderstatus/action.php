<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderStatus;

use Bitrix\Sale;
use Bitrix\Main;
use Sale\Handlers\PaySystem\YandexPayHandler;
use YandexPay\Pay;
use YandexPay\Pay\Trading\Entity\Registry as EntityRegistry;
use YandexPay\Pay\Trading\Action\Rest;

class Action extends Pay\Reference\Event\Regular
{
	public static function getHandlers() : array
	{
		return [
			[
				'module' => 'sale',
				'event' => 'OnSaleStatusOrderChange',
				'method' => 'processChangeStatus',
				'sort' => 1000
			],
		];
	}

	public static function processChangeStatus(Main\Event $event) : Main\EventResult
	{
		$result = new Main\EventResult(Main\EventResult::SUCCESS, null, 'sale');

		try
		{
			if (!Main\Loader::includeModule('yandexpay.pay')) { return $result; }

			(new Rest\Pipeline())
				->pipe(new Rest\OrderStatus\Stage\Load($event))
				->pipe(new Rest\OrderStatus\Stage\CheckStatus())
				->process(new Rest\State\OrderStatus());

		}
		catch (\Throwable $exception)
		{
			//nothing
		}

		return $result;
	}
}