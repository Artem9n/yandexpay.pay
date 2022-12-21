<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Dpd;

use Bitrix\Main;
use Bitrix\Sale;
use Ipolh\DPD\Delivery\DPD;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\AbstractAdapter;

abstract class Base extends AbstractAdapter
{
	protected $code = '';
	protected $title;

	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof Sale\Delivery\Services\AutomaticProfile)) { return false; }

		$code = $service->getCode();

		$this->title = $service->getName();

		return $code === $this->code;
	}

	public function load() : bool
	{
		return Main\Loader::includeModule('ipol.dpd');
	}

	protected function calculateAndFillSessionValues(Sale\Order $order) : void
	{
		$shipments = $order->getShipmentCollection();

		/** @var Sale\Shipment $shipment */
		foreach ($shipments as $shipment)
		{
			if ($shipment->isSystem()) { continue; }

			$shipment->calculateDelivery();
		}

		$arResult['DELIVERY'] = [
			$this->code => [
				'ID' => $this->code,
				'PERIOD_TEXT' => '',
				'CHECKED' => 'Y'
			]
		];
		// заполняет $_SESSION['IPOLH_DPD_ORDER'] и $_SESSION['IPOLH_DPD_TARIFF']
		DPD::OnSaleDeliveryHiddenHTML($arResult, [], []);
	}

	public function onAfterOrderSave(Sale\OrderBase $order) : void
	{
		$key = Main\Config\Option::get(IPOLH_DPD_MODULE, 'ORDER_ID', 'ID');
		$orderId = $order->getField($key);
		$entity  = \Ipolh\DPD\DB\Order\Table::findByOrder($orderId, true);

		$profile = DPD::getDeliveryProfile($this->code);


		if ($entity->id) {
			return;
		}

		$entity->serviceCode          = $_REQUEST['IPOLH_DPD_TARIFF'][$profile];
		$entity->serviceVariant       = $profile;
		$entity->receiverTerminalCode = $_REQUEST['IPOLH_DPD_TERMINAL'][$profile] ?: null;

		$entity->save();

		unset($_SESSION['IPOLH_DPD_ORDER']);
		unset($_SESSION['IPOLH_DPD_TARIFF']);
	}
}
