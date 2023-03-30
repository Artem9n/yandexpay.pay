<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Dpd;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery as EntityDelivery;

class Courier extends Base
{
	protected $codeService = 'ipolh_dpd:COURIER';

	public function serviceType() : string
	{
		return  EntityDelivery::COURIER_TYPE;
	}

	public function markSelectedCourier(Sale\Order $order, string $address, string $zip) : void
	{
		$this->fillAddress($order, $address);
		$this->calculateAndFillSessionValues($order);
	}

	public function prepareCalculateCourier(Sale\Order $order) : void
	{
		$paymentCollection = $order->getPaymentCollection();

		/** @var Sale\Payment $payment */
		foreach ($paymentCollection as $payment)
		{
			if ($payment->isInner()) { continue; }

			$_REQUEST['PAY_SYSTEM_ID'] = $payment->getPaymentSystemId();
		}
	}
}