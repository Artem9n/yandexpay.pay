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

	protected function elementDefaults(array $context = []) : array
	{
		return [
			'SELECTOR' => '.product-item-button-container',
			'POSITION' => 'beforeend',
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

	protected function basketFlyDefaults(array $context = []) : array
	{
		$design = [
			'HEIGHT_VALUE_BUTTON' => 44,
		] + $this->designDefaults();

		$settings = [
			'SELECTOR' => '#bx_basketFKauiI .basket-list-footer .right',
			'PATH' => '*',
			'POSITION' => 'beforeend',
		];

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
		$elementSettings['QUERY_CHECK_PARAMS'] = 'is_fast_view=Y';

		return $elementSettings;
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