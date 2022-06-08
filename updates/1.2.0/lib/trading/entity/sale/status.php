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

	public const EVENT_ORDER = 'ORDER_STATUS_UPDATED'; //��������� ������� ������� ��� ��������
	public const EVENT_OPERATION = 'OPERATION_STATUS_UPDATED'; //��������� ������� �������� �� �������������, ������ ��� �������� �������

	public const PAYMENT_STATUS_PENDING = 'PENDING'; //��������� ������.
	public const PAYMENT_STATUS_AUTHORIZE = 'AUTHORIZED'; //������ �� ����� �����������. �������� ������������� �� ����� �����������.
	public const PAYMENT_STATUS_CAPTURE = 'CAPTURED'; //����� ������� �������. �������� ������� �� ����� �����������.
	public const PAYMENT_STATUS_VOID = 'VOIDED'; //������ �������� (voided). �������� ������� �� �������������.
	public const PAYMENT_STATUS_REFUND = 'REFUNDED'; //�������� ������� ������� �� �����.
	public const PAYMENT_STATUS_PARTIAL_REFUND = 'PARTIALLY_REFUNDED'; //�������� ��������� ������� ������� �� �����
	public const PAYMENT_STATUS_FAIL = 'FAILED'; //����� �� ��� ������� �������.

	public const ORDER_STATUS_CAPTURE = 'YC'; // ������ ������������
	public const ORDER_STATUS_AUTHORIZE = 'YH'; // ������ ������������ (������������ ��� �����������)
	public const ORDER_STATUS_REFUND = 'YR'; // ������� ������
	public const ORDER_STATUS_PARTIALLY_REFUND = 'YP'; // ��������� ������� ������
	public const ORDER_STATUS_VOID = 'YU'; // ������ ������

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