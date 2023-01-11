<?php
namespace YandexPay\Pay\Injection\Solution;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Injection\Behavior;

class Aspro extends Skeleton
{
	use Concerns\HasMessage;

	public function getTitle() : string
	{
		return self::getMessage('TITLE');
	}

	public function getType() : string
	{
		return Registry::ASPRO;
	}

	public function isMatch(array $context = []) : bool
	{
		return Utils::matchTemplates('aspro', $context);
	}

	public function getOrderPath(array $context = []) : string
	{
		return Guide::getBitrixOrderPath($context, '/order/');
	}

	public function getDefaults(array $context = []) : array
	{
		$selectors = [
			'.buy_block .offer_buy_block',
			'.buy_block .wrapp-one-click',
			'.buy_block .wrapp_one_click',
			'.buy_block .counter_wrapp',
			'.buy_block .buttons',
		];

		$design = [
			'DISPLAY' => Behavior\Display\Registry::BUTTON,
			'HEIGHT_TYPE_BUTTON' => 'OWN',
			'HEIGHT_VALUE_BUTTON' => 49,
			'BORDER_RADIUS_TYPE_BUTTON' => 'OWN',
			'BORDER_RADIUS_VALUE_BUTTON' => 3,
			'USE_DIVIDER' => true,
		];

		$elementSettings = [
			'SELECTOR' => implode(', ', $selectors),
			'POSITION' => 'afterend',
			'IBLOCK' => $context['IBLOCK'],
			'WIDTH_BUTTON' => 'MAX',
		];

		return [
			Behavior\Registry::ELEMENT => $design + $elementSettings,
			Behavior\Registry::ELEMENT_FAST => $design + $elementSettings + [
				'QUERY_CHECK_PARAMS' => 'FAST_VIEW=Y'
			],

			Behavior\Registry::BASKET => $design + Guide::getBitrixBasket($context, '/basket/'),
			Behavior\Registry::BASKET_FLY => [
				'SELECTOR' => '.header-cart.fly .basket_back',
				'USE_DIVIDER' => false,
				'WIDTH_BUTTON' => 'MAX'
			] + $design + Guide::getBitrixBasket($context, '/ajax/basket_fly.php'),

			Behavior\Registry::ORDER => $design + Guide::getBitrixOrder($context, '/order/'),
		];
	}
}