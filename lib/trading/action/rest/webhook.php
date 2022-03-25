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
	 * id заказа, пришедший в /api/v1/checkout в теле в sheet.order.id
	 * @return string
	 */
	public function getExternalOrderId() : string
	{
		return $this->requireField('data.externalOrderId');
	}

	/**
	 * Статус заказа
	 * [
	 *  HOLD - Заказ подтвержден, рассрочка выдана либо заблокированы средства на карте
	 *  SUCCESS - Рассрочка полностью выплачена, либо заблокированные средства списаны
	 *  PARTIAL_REFUND - Заказ отменен частично, размер рассрочки пересчитан
	 *  REFUND - Заказ отменен полностью
	 *  FAIL - Рассрочка не выдана, либо оплата не прошла
	 * ]
	 * @return string
	 */
	public function getStatus() : string
	{
		return $this->requireField('data.status');
	}

	/**
	 * Время события в формате `RFC 3339`; `YYYY-MM-DDThh:mm:ssTZD`
	 * @return string
	 */
	public function getEventTime() : string
	{
		return $this->requireField('data.eventTime');
	}
}