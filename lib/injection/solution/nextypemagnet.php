<?php
namespace YandexPay\Pay\Injection\Solution;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Injection\Behavior;

class NextypeMagnet extends Skeleton
{
	use Concerns\HasMessage;

	public function getTitle() : string
	{
		return self::getMessage('TITLE');
	}

	public function getType() : string
	{
		return Registry::NEXTYPE_MAGNET;
	}

	public function isMatch(array $context = []) : bool
	{
		return Utils::matchTemplates('magnet', $context);
	}

	public function getOrderPath(array $context = []) : string
	{
		return Guide::getBitrixOrderPath($context, '/order/');
	}

	public function getDefaults(array $context = []) : array
	{
		return [
			Behavior\Registry::ELEMENT => [
				'SELECTOR' => '.product-main-info .info .order-container .buttons .product-item-button-container div',
				'POSITION' => 'afterend',
				'IBLOCK' => $context['IBLOCK'],
				'DISPLAY' => Behavior\Display\Registry::BUTTON,
				'WIDTH_BUTTON' => 'MAX',
				'HEIGHT_TYPE_BUTTON' => 'OWN',
				'HEIGHT_VALUE_BUTTON' => 42,
				'BORDER_RADIUS_TYPE_BUTTON' => 'OWN',
				'BORDER_RADIUS_VALUE_BUTTON' => 30,
			],
			Behavior\Registry::BASKET => [
					'HEIGHT_TYPE_BUTTON' => 'OWN',
					'HEIGHT_VALUE_BUTTON' => 44,
					'BORDER_RADIUS_TYPE_BUTTON' => 'OWN',
					'BORDER_RADIUS_VALUE_BUTTON' => 30,
					'WIDTH_BUTTON' => 'MAX',
				]
				+ Guide::getBitrixBasket($context),
			Behavior\Registry::ORDER => [
					'HEIGHT_TYPE_BUTTON' => 'OWN',
					'HEIGHT_VALUE_BUTTON' => 52,
					'BORDER_RADIUS_TYPE_BUTTON' => 'OWN',
					'BORDER_RADIUS_VALUE_BUTTON' => 30,
					'WIDTH_BUTTON' => 'MAX',
					'POSITION' => 'afterend',
				]
				+ Guide::getBitrixOrder($context),
		];
	}
}