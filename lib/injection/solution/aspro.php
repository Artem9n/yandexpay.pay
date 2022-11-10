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
			'HEIGHT_BUTTON' => 'CUSTOM',
			'BORDER_RADIUS_BUTTON' => 'CUSTOM',
			'HEIGHT_VALUE' => 49,
			'BORDER_RADIUS_VALUE' => 3,
			'USE_DIVIDER' => true,
		];

		return [
			Behavior\Registry::ELEMENT => $design + [
				'SELECTOR' => implode(', ', $selectors),
				'POSITION' => 'afterend',
				'IBLOCK' => $context['IBLOCK'],
				'WIDTH_BUTTON' => 'MAX',
			],
			Behavior\Registry::BASKET => $design + Guide::getBitrixBasket($context, '/basket/'),
			Behavior\Registry::ORDER => $design + Guide::getBitrixOrder($context, '/order/'),
		];
	}
}