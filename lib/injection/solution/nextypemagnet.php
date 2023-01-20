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

	protected function designDefaults() : array
	{
		return [
			'DISPLAY' => Behavior\Display\Registry::BUTTON,
			'HEIGHT_TYPE_BUTTON' => 'OWN',
			'BORDER_RADIUS_TYPE_BUTTON' => 'OWN',
			'BORDER_RADIUS_VALUE_BUTTON' => 30,
			'WIDTH_BUTTON' => 'MAX',
		];
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

	protected function elementDefaults(array $context = []) : array
	{
		return [
				'SELECTOR' => '.product-main-info .info .order-container .buttons .product-item-button-container div',
				'POSITION' => 'afterend',
				'IBLOCK' => $context['IBLOCK'],
				'HEIGHT_VALUE_BUTTON' => 42,
			] + $this->designDefaults();
	}

	protected function basketDefaults(array $context = []) : array
	{
		$design = [
			'HEIGHT_VALUE_BUTTON' => 44,
		] + $this->designDefaults();

		$settings = Guide::getBitrixBasket($context);

		return $design + $settings;
	}

	protected function orderDefaults(array $context = []) : array
	{
		$design = [
			'HEIGHT_VALUE_BUTTON' => 52,
			'POSITION' => 'afterend',
			] + $this->designDefaults();

		$settings = Guide::getBitrixOrder($context);

		return $design + $settings;
	}

	protected function elementFastDefaults(array $context = []) : array
	{
		$elementSettings = $this->elementDefaults($context);
		$elementSettings['QUERY_CHECK_PARAMS'] = 'FAST_VIEW=Y';

		return $elementSettings;
	}

	public function getDefaults(array $context = []) : array
	{
		return [
			Behavior\Registry::ELEMENT => $this->elementDefaults($context),
			Behavior\Registry::BASKET => $this->basketDefaults($context),
			Behavior\Registry::ORDER => $this->orderDefaults($context),
			Behavior\Registry::BASKET_FLY => $this->basketFlyDefaults($context),
			Behavior\Registry::ELEMENT_FAST => $this->elementFastDefaults($context),
		];
	}
}