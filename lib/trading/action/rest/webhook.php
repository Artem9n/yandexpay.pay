<?php
namespace YandexPay\Pay\Trading\Action\Rest;

use YandexPay\Pay\Trading\Action\Reference;

class Webhook extends Reference\Dto
{
	public function getEvent() : string
	{
		return $this->requireField('event');
	}

	/**
	 * id ������, ��������� � /api/v1/checkout � ���� � sheet.order.id
	 * @return string
	 */
	public function getExternalOrderId() : string
	{
		return $this->requireField('data.externalOrderId');
	}

	/**
	 * ������ ������
	 * [
	 *  HOLD - ����� �����������, ��������� ������ ���� ������������� �������� �� �����
	 *  SUCCESS - ��������� ��������� ���������, ���� ��������������� �������� �������
	 *  PARTIAL_REFUND - ����� ������� ��������, ������ ��������� ����������
	 *  REFUND - ����� ������� ���������
	 *  FAIL - ��������� �� ������, ���� ������ �� ������
	 * ]
	 * @return string
	 */
	public function getStatus() : string
	{
		return $this->requireField('data.status');
	}

	/**
	 * ����� ������� � ������� `RFC 3339`; `YYYY-MM-DDThh:mm:ssTZD`
	 * @return string
	 */
	public function getEventTime() : string
	{
		return $this->requireField('data.eventTime');
	}
}