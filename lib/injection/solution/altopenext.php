<?php
namespace YandexPay\Pay\Injection\Solution;

use YandexPay\Pay\Injection\Behavior;
use YandexPay\Pay\Injection\Engine;
use YandexPay\Pay\Reference\Concerns;

class AltopEnext extends Skeleton
{
	use Concerns\HasMessage;

	public function getTitle() : string
	{
		return self::getMessage('TITLE');
	}

	public function getType() : string
	{
		return Registry::ALTOP_ENEXT;
	}

	public function isMatch(array $context = []) : bool
	{
		return Utils::matchTemplates('enext', $context);
	}

	public function getOrderPath(array $context = []) : string
	{
		return Guide::path($context);
	}

	protected function designDefaults() : array
	{
		return [
			'DISPLAY' => Behavior\Display\Registry::BUTTON,
			'HEIGHT_TYPE_BUTTON' => 'OWN',
			'HEIGHT_VALUE_BUTTON' => 48,
			'BORDER_RADIUS_TYPE_BUTTON' => 'OWN',
			'BORDER_RADIUS_VALUE_BUTTON' => 4,
			'WIDTH_BUTTON' => 'MAX',
			'POSITION' => 'afterend',
		];
	}

	protected function elementDefaults(array $context = []) : array
	{
		return [
			'SELECTOR' => '.product-item-detail-pay-block .product-item-detail-button-container',
			'IBLOCK' => $context['IBLOCK'],
		] + $this->designDefaults();
	}

	protected function elementFastDefaults(array $context = []) : array
	{
		$selectors = implode(', ', [
			'.popup-panel .product-item-detail-article-button-container',
			'.popup-panel .product-item-detail-pay-block .product-item-detail-button-container',
		]);

		$elementSettings = $this->elementDefaults($context);
		$elementSettings['QUERY_CHECK_PARAMS'] = 'action=quickViewFull&action=quickView';
		$elementSettings['QUERY_ELEMENT_ID_PARAM'] = 'productId';
		$elementSettings['SELECTOR'] = $selectors;

		return $elementSettings;
	}

	protected function basketDefaults(array $context = []) : array
	{
		$design = [
			'HEIGHT_VALUE_BUTTON' => 42,
			'POSITION' => 'beforeend',
		] + $this->designDefaults();

		$settings = [
			'SELECTOR' => '.bx_ordercart_order_pay .bx_ordercart_order_pay_right',
		] + Guide::getBitrixBasket($context);

		return $design + $settings;
	}

	protected function basketFlyDefaults(array $context = []) : array
	{
		return [
			'SELECTOR' => '.slide-panel-basket-footer',
			'PATH' => Guide::path($context, '/ajax/slide_panel.php'),
			'HEIGHT_VALUE_BUTTON' => 42,
			'POSITION' => 'beforeend',
		] + $this->designDefaults();
	}

	protected function orderDefaults(array $context = []) : array
	{
		$design = [
			'HEIGHT_VALUE_BUTTON' => 42,
			'POSITION' => 'beforeend',
		] + $this->designDefaults();

		$settings = Guide::getBitrixOrder($context);

		return $design + $settings;
	}

	public function getDefaults(array $context = []) : array
	{
		return [
			Behavior\Registry::ELEMENT => $this->elementDefaults($context),
			Behavior\Registry::ELEMENT_FAST => $this->elementFastDefaults($context),
			Behavior\Registry::BASKET => $this->basketDefaults($context),
			Behavior\Registry::BASKET_FLY => $this->basketFlyDefaults($context),
			Behavior\Registry::ORDER => $this->orderDefaults($context)
		];
	}

	public function eventSettings(Behavior\BehaviorInterface $behavior) : array
	{
		if (
			$behavior instanceof Behavior\BasketFly
			|| $behavior instanceof Behavior\ElementFast
		)
		{
			return [
				'RENDER' => Engine\AbstractEngine::RENDER_OUTPUT,
			];
		}

		return [];
	}
}