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
		return Utils::matchTemplates('eshop_bootstrap', $context);
	}

	public function getOrderPath(array $context = []) : string
	{
		return Guide::getBitrixOrderPath($context);
	}

	public function getDefaults(array $context = []) : array
	{
		return [
			Behavior\Registry::ELEMENT => [
				'SELECTOR' => '.product-item-detail-slider-container',
				'POSITION' => 'beforeend',
				'IBLOCK' => $context['IBLOCK'],
			],
			Behavior\Registry::BASKET => Guide::getBitrixBasket($context),
			Behavior\Registry::ORDER => Guide::getBitrixOrder($context),
		];
	}
}