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

	public function getDefaults(array $context = []) : array
	{
		return [
			Behavior\Registry::ELEMENT => [
				'SELECTOR' => '.product-item-detail-pay-block .product-item-detail-button-container',
				'POSITION' => 'afterend',
				'IBLOCK' => 29,
				'DISPLAY' => Behavior\Display\Registry::BUTTON,
				'WIDTH_BUTTON' => 'MAX',
				'HEIGHT_TYPE_BUTTON' => 'OWN',
				'HEIGHT_VALUE_BUTTON' => 48,
				'BORDER_RADIUS_TYPE_BUTTON' => 'OWN',
				'BORDER_RADIUS_VALUE_BUTTON' => 4,
			],
			Behavior\Registry::BASKET => [
					'HEIGHT_TYPE_BUTTON' => 'OWN',
					'HEIGHT_VALUE_BUTTON' => 42,
					'BORDER_RADIUS_TYPE_BUTTON' => 'OWN',
					'BORDER_RADIUS_VALUE_BUTTON' => 4,
					'WIDTH_BUTTON' => 'MAX',
				]
				+ Guide::getBitrixBasket($context),
			Behavior\Registry::ORDER => [
					'HEIGHT_TYPE_BUTTON' => 'OWN',
					'HEIGHT_VALUE_BUTTON' => 46,
					'BORDER_RADIUS_TYPE_BUTTON' => 'OWN',
					'BORDER_RADIUS_VALUE_BUTTON' => 4,
					'WIDTH_BUTTON' => 'MAX',
					'POSITION' => 'afterend',
				]
				+ Guide::getBitrixOrder($context),

		];
	}
}