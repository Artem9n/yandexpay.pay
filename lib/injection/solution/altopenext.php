<?php
namespace YandexPay\Pay\Injection\Solution;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Injection\Behavior;

class AltopEnext extends Skeleton
{
	use Concerns\HasMessage;

	public function getTitle() : string
	{
		return self::getMessage('TITLE');
	}

	public function getType() : string
	{
		return Registry::ALTOP_ENEXT;
	}

	public function isMatch(array $context = []) : bool
	{
		return true;
	}

	public function getOrderPath(array $context = []) : string
	{
		return Guide::getBitrixOrderPath($context);
	}

	protected function designDefaults() : array
	{
		return [
			'DISPLAY' => Behavior\Display\Registry::BUTTON,
			'HEIGHT_TYPE_BUTTON' => 'OWN',
			'BORDER_RADIUS_TYPE_BUTTON' => 'OWN',
			'BORDER_RADIUS_VALUE_BUTTON' => 4,
			'WIDTH_BUTTON' => 'MAX',
		];
	}

	protected function elementDefaults(array $context = []) : array
	{
		return [
			'SELECTOR' => '.product-item-detail-pay-block .product-item-detail-button-container',
			'POSITION' => 'afterend',
			'IBLOCK' => 29,
			'HEIGHT_VALUE_BUTTON' => 48,
		] + $this->designDefaults();
	}

	protected function basketDefaults(array $context = []) : array
	{
		$design = [
				'SELECTOR' => '.bx_ordercart_order_pay .bx_ordercart_order_pay_right',
				'HEIGHT_TYPE_BUTTON' => 'OWN',
				'HEIGHT_VALUE_BUTTON' => 42,
				'POSITION' => 'beforeend',
			] + $this->designDefaults();

		$settings = Guide::getBitrixBasket($context);

		return $design + $settings;
	}

	protected function orderDefaults(array $context = []) : array
	{
		$design = [
				'HEIGHT_TYPE_BUTTON' => 'OWN',
				'HEIGHT_VALUE_BUTTON' => 46,
				'POSITION' => 'beforeend',
			] + $this->designDefaults();

		$settings = Guide::getBitrixOrder($context);

		return $design + $settings;
	}


	public function getDefaults(array $context = []) : array
	{
		return [
			Behavior\Registry::ELEMENT => $this->elementDefaults($context),
			Behavior\Registry::BASKET => $this->basketDefaults($context),
			Behavior\Registry::ORDER => $this->orderDefaults($context)
		];
	}
}