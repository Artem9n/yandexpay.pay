<?php

namespace YandexPay\Pay\Trading\Entity\Sale;

use Bitrix\Sale;
use Bitrix\Main\Loader;
use YandexPay\Pay\Reference\Concerns;
use Bitrix\Main\Localization\LanguageTable;

class Status
{
	use Concerns\HasMessage;

	protected static $statusList;

	public const EVENT_ORDER = 'ORDER_STATUS_UPDATED'; //изменение статуса платежа или доставки
	public const EVENT_OPERATION = 'OPERATION_STATUS_UPDATED'; //изменение статуса операции по подтверждению, отмене или возврату платежа

	public const PAYMENT_STATUS_PENDING = 'PENDING'; //Ожидается оплата.
	public const PAYMENT_STATUS_AUTHORIZE = 'AUTHORIZED'; //Платеж за заказ авторизован. Средства заблокированы на счету плательщика.
	public const PAYMENT_STATUS_CAPTURE = 'CAPTURED'; //Заказ успешно оплачен. Средства списаны со счета плательщика.
	public const PAYMENT_STATUS_VOID = 'VOIDED'; //Оплата отменена (voided). Списание средств не производилось.
	public const PAYMENT_STATUS_REFUND = 'REFUNDED'; //Совершён возврат средств за заказ.
	public const PAYMENT_STATUS_PARTIAL_REFUND = 'PARTIALLY_REFUNDED'; //Совершён частичный возврат средств за заказ
	public const PAYMENT_STATUS_FAIL = 'FAILED'; //Заказ не был успешно оплачен.

	public const ORDER_STATUS_CAPTURE = 'YC'; // оплата подтверждена
	public const ORDER_STATUS_AUTHORIZE = 'YH'; // оплата авторизована (холдирование при двухстадики)
	public const ORDER_STATUS_REFUND = 'YR'; // возврат оплаты
	public const ORDER_STATUS_PARTIALLY_REFUND = 'YP'; // частичный возврат оплаты
	public const ORDER_STATUS_VOID = 'YU'; // отмена оплаты

	public static function getEnum() : array
	{
		if (static::$statusList === null)
		{
			static::$statusList = static::loadStatusList();
		}

		return static::$statusList;
	}

	public static function getStatusList() : array
	{
		return [
			static::ORDER_STATUS_CAPTURE,
			static::ORDER_STATUS_AUTHORIZE,
			static::ORDER_STATUS_REFUND,
			static::ORDER_STATUS_PARTIALLY_REFUND,
			static::ORDER_STATUS_VOID,
		];
	}

	public static function orderCapture() : string
	{
		return static::ORDER_STATUS_CAPTURE;
	}

	public static function orderAuthorize() : string
	{
		return static::ORDER_STATUS_AUTHORIZE;
	}

	public static function orderRefund() : string
	{
		return static::ORDER_STATUS_REFUND;
	}

	public static function orderPartiallyRefund() : string
	{
		return static::ORDER_STATUS_PARTIALLY_REFUND;
	}

	public static function orderCancel() : string
	{
		return static::ORDER_STATUS_VOID;
	}

	protected static function loadStatusList() : array
	{
		$result = [];

		$query = Sale\Internals\StatusTable::getList([
			'filter' => [
				'TYPE' => \Bitrix\Sale\OrderStatus::TYPE,
			],
			'select' => [
				'ID',
				'NAME' => 'STATUS_LANG.NAME',
			],
		]);

		while ($status = $query->fetch())
		{
			$result[$status['ID']] = sprintf('[%s] %s', $status['ID'], $status['NAME']);
		}

		return $result;
	}

	public static function install() : void
	{
		if (!Loader::includeModule('sale')) { return; }
		if (!Loader::includeModule('main')) { return; }

		$languages = [];

		// get languages
		$result = LanguageTable::getList([
			'select' => ['LID', 'NAME'],
			'filter' => ['=ACTIVE' => 'Y']
		]);
		while ($row = $result->fetch())
		{
			$languages[$row['LID']] = $row['NAME'];
		}

		foreach (static::getStatusList() as $statusId)
		{
			try {

				$status = [
					'ID' => $statusId,
					'TYPE'   => \Bitrix\Sale\OrderStatus::TYPE,
					'SORT'   => 100,
					'NOTIFY' => 'Y',
					'COLOR' => '',
					'XML_ID' => Sale\Internals\StatusTable::generateXmlId(),
				];

				$result = Sale\Internals\StatusTable::add($status);

				if ($result->isSuccess())
				{
					foreach ($languages as $languageId => $languageName)
					{
						$codeName = sprintf('NAME_%s' , $statusId);
						$codeDescription = sprintf('DESCRIPTION_%s' , $statusId);

						$translationName = trim(static::getMessage($codeName, null, null, $languageId));
						$translationDescription = trim(static::getMessage($codeDescription,null, null, $languageId));

						$translations = [
							'STATUS_ID'   => $statusId,
							'LID'         => $languageId,
							'NAME'        => $translationName,
							'DESCRIPTION' => $translationDescription,
						];

						Sale\StatusLangTable::add($translations);
					}

					$saleStatus = new \CSaleStatus();
					$saleStatus->CreateMailTemplate($statusId);
				}
			}
			catch (\Throwable $exception)
			{
				continue;
			}
		}
	}
}