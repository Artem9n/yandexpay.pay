<?php
namespace YandexPay\Pay\Injection\Solution;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Injection\Behavior;

class AsproOptimus extends Skeleton
{
	use Concerns\HasMessage;

	public function getTitle() : string
	{
		return self::getMessage('TITLE');
	}

	public function getType() : string
	{
		return Registry::ASPRO_OPTIMUS;
	}

	public function isMatch(array $context = []) : bool
	{
		return Utils::matchTemplates('aspro_optimus', $context);
	}

	public function getOrderPath(array $context = []) : string
	{
		return Guide::getBitrixOrderPath($context, '/order/');
	}

	public function getDefaults(array $context = []) : array
	{
		return [
			Behavior\Registry::ELEMENT => [
				'SELECTOR' => '.buy_block .offer_buy_block, .buy_block .wrapp_one_click, .buy_block .counter_wrapp',
				'POSITION' => 'afterend',
				'IBLOCK' => $context['IBLOCK'],
				'WIDTH_BUTTON' => 'MAX',
			],
			Behavior\Registry::BASKET => Guide::getBitrixBasket($context, '/basket/'),
			Behavior\Registry::ORDER => Guide::getBitrixOrder($context, '/order/'),
		];
	}
}