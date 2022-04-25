<?php

namespace YandexPay\Pay\Trading\Entity\Sale;

use Bitrix\Sale;

class Status
{
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

	public const ORDER_STATUS_CAPTURE = 'YC';
	public const ORDER_STATUS_AUTHORIZE = 'YH';
	public const ORDER_STATUS_REFUND = 'YR';
	public const ORDER_STATUS_VOID = 'YU';

	public static function getEnum() : array
	{
		if (static::$statusList === null)
		{
			static::$statusList = static::loadStatusList();
		}

		return static::$statusList;
	}

	protected static function loadStatusList() : array
	{
		$result = [];

		$query = Sale\Internals\StatusTable::getList([
			'filter' => [
				'TYPE' => 'O',
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
}