<?php
namespace YandexPay\Pay\Injection\Solution;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Injection\Behavior;

class EshopBootstrap extends Skeleton
{
	use Concerns\HasMessage;

	public function getTitle() : string
	{
		return self::getMessage('TITLE');
	}

	public function getType() : string
	{
		return Registry::ESHOP_BOOTSTRAP;
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
				'SELECTOR' => '.col-md-6.col-sm-12 .row, .col-md-5.col-lg-6 .row',
				'POSITION' => 'afterend',
				'IBLOCK' => $context['IBLOCK'],
				'DISPLAY' => Behavior\Display\Registry::BUTTON,
			],
			Behavior\Registry::BASKET => Guide::getBitrixBasket($context),
			Behavior\Registry::ORDER => Guide::getBitrixOrder($context),
		];
	}
}