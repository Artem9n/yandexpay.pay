<?php

namespace YandexPay\Pay\Trading\Entity\Sale;

use Bitrix\Sale;

class Status
{
	protected static $statusList;

	public const EVENT_ORDER = 'ORDER_STATUS_UPDATED'; //��������� ������� ������� ��� ��������
	public const EVENT_OPERATION = 'OPERATION_STATUS_UPDATED'; //��������� ������� �������� �� �������������, ������ ��� �������� �������

	public const PAYMENT_STATUS_PENDING = 'PENDING'; //��������� ������.
	public const PAYMENT_STATUS_AUTHORIZE = 'AUTHORIZED'; //������ �� ����� �����������. �������� ������������� �� ����� �����������.
	public const PAYMENT_STATUS_CAPTURE = 'CAPTURED'; //����� ������� �������. �������� ������� �� ����� �����������.
	public const PAYMENT_STATUS_VOID = 'VOIDED'; //������ �������� (voided). �������� ������� �� �������������.
	public const PAYMENT_STATUS_REFUND = 'REFUNDED'; //�������� ������� ������� �� �����.
	public const PAYMENT_STATUS_PARTIAL_REFUND = 'PARTIALLY_REFUNDED'; //�������� ��������� ������� ������� �� �����
	public const PAYMENT_STATUS_FAIL = 'FAILED'; //����� �� ��� ������� �������.

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