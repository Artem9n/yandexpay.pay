<?php
namespace YandexPay\Pay\Injection\Solution;

use Bitrix\Main\Loader;
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
		return 'Aspro';
	}

	public function isMatch(array $context = []) : bool
	{
		return Utils::matchTemplates('aspro', $context);
	}

	public function getOrderPath(array $context = []) : string
	{
		return Guide::getBitrixOrderPath($context, '/order/');
	}

	protected function designDefaults() : array
	{
		return [
			'DISPLAY' => Behavior\Display\Registry::BUTTON,
			'HEIGHT_TYPE_BUTTON' => 'OWN',
			'HEIGHT_VALUE_BUTTON' => 49,
			'BORDER_RADIUS_TYPE_BUTTON' => 'OWN',
			'BORDER_RADIUS_VALUE_BUTTON' => 3,
			'USE_DIVIDER' => true,
		];
	}

	protected function elementDefaults(array $context = []) : array
	{
		$design = $this->designDefaults();

		$selectors = implode(', ', [
			'.buy_block .offer_buy_block',
			'.buy_block .wrapp-one-click',
			'.buy_block .wrapp_one_click',
			'.buy_block .counter_wrapp',
			'.buy_block .buttons',
		]);

		$settings = [
			'SELECTOR' => $selectors,
			'POSITION' => 'afterend',
			'IBLOCK' => $context['IBLOCK'],
			'WIDTH_BUTTON' => 'MAX',
		];

		return $design + $settings;
	}

	protected function elementFastDefaults(array $context = []) : array
	{
		$elementSettings = $this->elementDefaults($context);
		$elementSettings['QUERY_CHECK_PARAMS'] = 'FAST_VIEW=Y';

		return $elementSettings;
	}

	protected function basketDefaults(array $context = []) : array
	{
		return $this->designDefaults() + Guide::getBitrixBasket($context, '/basket/');
	}

	protected function orderDefaults(array $context = []) : array
	{
		return $this->designDefaults() + Guide::getBitrixOrder($context, '/order/');
	}

	protected function basketFlyDefaults(array $context = []) : array
	{
		$paths = [
			'/ajax/basket_fly.php',
			'/ajax/showBasketHover.php',
		];

		foreach ($paths as &$path)
		{
			$dir = $context['SITE_DIR'] ?? '';
			$dir = rtrim($dir, '/');
			$path = $dir . $path;
		}
		unset($path);

		$settings = [
			'SELECTOR' => '.header-cart.fly .basket_back, .basket_hover_block .basket_wrap .buttons',
			'USE_DIVIDER' => false,
			'WIDTH_BUTTON' => 'MAX',
			'PATH' => implode(PHP_EOL, $paths),
			'POSITION' => 'afterend',
		];

		$design = $this->designDefaults();

		return $settings + $design;
	}

	public function getDefaults(array $context = []) : array
	{
		return [
			Behavior\Registry::ELEMENT => $this->elementDefaults($context),
			Behavior\Registry::ELEMENT_FAST => $this->elementFastDefaults($context),
			Behavior\Registry::BASKET => $this->basketDefaults($context),
			Behavior\Registry::BASKET_FLY => $this->basketFlyDefaults($context),
			Behavior\Registry::ORDER => $this->orderDefaults($context),
		];
	}
}