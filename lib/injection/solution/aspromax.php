<?php
namespace YandexPay\Pay\Injection\Solution;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Injection\Behavior;

class AsproMax extends Skeleton
{
	use Concerns\HasMessage;

	public function getTitle() : string
	{
		return self::getMessage('TITLE');
	}

	public function getType() : string
	{
		return Registry::ASPRO_MAX;
	}

	public function isMatch(array $context = []) : bool
	{
		return Utils::matchTemplates('aspro_max', $context);
	}

	public function getDefaults(array $context = []) : array
	{
		return [
			Behavior\Registry::ELEMENT => [
				'SELECTOR' => '.buy_block .counter_wrapp',
				'POSITION' => 'beforeend',
				'IBLOCK' => $context['IBLOCK'],
				'WIDTH_BUTTON' => 'MAX'
			],
			Behavior\Registry::BASKET => Guide::getBitrixBasket($context, '/basket/'),
			Behavior\Registry::ORDER => Guide::getBitrixOrder($context, '/order/'),
		];
	}
}