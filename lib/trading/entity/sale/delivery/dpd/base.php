<?php
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */
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

	public function prepareCalculation(Sale\Order $Order) : void
	{
		$paymentCollection = $Order->getPaymentCollection();

		/** @var Sale\Payment $payment */
		foreach ($paymentCollection as $payment)
		{
			if ($payment->isInner()) { continue; }

			$_REQUEST['PAY_SYSTEM_ID'] = $payment->getPaymentSystemId();
		}
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

	public function onAfterOrderSave(Sale\Order $order) : void
	{
		/** @noinspection PhpUndefinedConstantInspection */
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

		unset($_SESSION['IPOLH_DPD_ORDER'], $_SESSION['IPOLH_DPD_TARIFF']);
	}
}
