<?php
namespace YandexPay\Pay\Injection\Solution\Aspro;

use YandexPay\Pay\Injection\Solution;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Injection\Behavior;

class Base extends Solution\Skeleton
{
	use Concerns\HasMessage;

	protected static $isMatch = false;

	public function getTitle() : string
	{
		return self::getMessage('TITLE');
	}

	public function getType() : string
	{
		return Solution\Registry::ASPRO_DEF;
	}

	public function isMatch(array $context = []) : bool
	{
		return (!static::$isMatch && Solution\Utils::matchTemplates('aspro', $context));
	}

	public function getOrderPath(array $context = []) : string
	{
		return Solution\Guide::path($context, '/order/');
	}

	protected function designDefaults() : array
	{
		return [
			'DISPLAY' => Behavior\Display\Registry::BUTTON,
			'HEIGHT_TYPE_BUTTON' => 'OWN',
			'HEIGHT_VALUE_BUTTON' => 49,
			'BORDER_RADIUS_TYPE_BUTTON' => 'OWN',
			'BORDER_RADIUS_VALUE_BUTTON' => 3,
			'WIDTH_BUTTON' => 'MAX',
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
		];

		return $design + $settings;
	}

	protected function elementFastDefaults(array $context = []) : array
	{
		$selectors = implode(', ', [
			'#fast_view_item .buy_block .offer_buy_block',
			'#fast_view_item .buy_block .wrapp-one-click',
			'#fast_view_item .buy_block .wrapp_one_click',
			'#fast_view_item .buy_block .counter_wrapp',
			'#fast_view_item .buy_block .buttons',
		]);

		return [
			'QUERY_CHECK_PARAMS' => 'FAST_VIEW=Y',
			'SELECTOR' => $selectors
		] + $this->elementDefaults($context);
	}

	protected function basketDefaults(array $context = []) : array
	{
		return  Solution\Guide::getBitrixBasket($context, '/basket/') + $this->designDefaults();
	}

	protected function orderDefaults(array $context = []) : array
	{
		return $this->designDefaults() + Solution\Guide::getBitrixOrder($context, '/order/');
	}

	protected function basketFlyDefaults(array $context = []) : array
	{
		$paths = [
			'/ajax/basket_fly.php',
			'/ajax/showBasketHover.php',
		];

		foreach ($paths as &$path)
		{
			$path = Solution\Guide::path($context, $path);
		}
		unset($path);

		$selectors = implode(', ', [
			'.header-cart.fly .buttons .basket_back',
			'.header-cart.fly .buttons .wrap_button',
			'.basket_hover_block .basket_wrap .buttons',
		]);

		$settings = [
			'SELECTOR' => $selectors,
			'PATH' => implode(PHP_EOL, $paths),
			'POSITION' => 'afterend',
		];

		$design = ['USE_DIVIDER' => false] + $this->designDefaults();

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