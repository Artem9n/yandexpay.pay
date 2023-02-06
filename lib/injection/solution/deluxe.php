<?php
namespace YandexPay\Pay\Injection\Solution;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Injection\Behavior;

class Deluxe extends Skeleton
{
	use Concerns\HasMessage;

	public function getTitle() : string
	{
		return self::getMessage('TITLE');
	}

	public function getType() : string
	{
		return Registry::DELUXE;
	}

	public function isMatch(array $context = []) : bool
	{
		return Utils::matchTemplates('dresscode', $context);
	}

	public function getOrderPath(array $context = []) : string
	{
		return Guide::path($context, '/personal/cart/order/');
	}

	protected function designDefaults() : array
	{
		return [
			'DISPLAY' => Behavior\Display\Registry::BUTTON,
			'BORDER_RADIUS_TYPE_BUTTON' => 'OWN',
			'BORDER_RADIUS_VALUE_BUTTON' => 4,
			'WIDTH_BUTTON' => 'MAX',
		];
	}

	protected function elementDesktopDefaults(array $context = []) : array
	{
		$design = [
			'WIDTH_BUTTON' => 'OWN',
			'WIDTH_VALUE_BUTTON' => 220
		] + $this->designDefaults();

		$settings = [
			'SELECTOR' => '.mobileButtonsContainer:media(min-width: 1101px)',
			'POSITION' => 'beforeend',
			'IBLOCK' => $context['IBLOCK'],
		];

		return $settings + $design;
	}

	protected function elementMobileDefaults(array $context = []) : array
	{
		$design = $this->designDefaults();

		$settings = [
			'SELECTOR' => '.mobileButtonsContainer:media(max-width: 1101px)',
			'POSITION' => 'beforeend',
			'IBLOCK' => $context['IBLOCK'],
		];

		return $design + $settings;
	}

	protected function elementFastDefaults(array $context = []) : array
	{
		$design = $this->designDefaults();

		$settings = [
			'IBLOCK' => $context['IBLOCK'],
			'SELECTOR' => '.catalogQtyBlock',
			'POSITION' => 'afterend',
			'QUERY_CHECK_PARAMS' => 'act=getFastView',
			'QUERY_ELEMENT_ID_PARAM' => 'product_id',
		];

		return $design + $settings;
	}

	protected function basketDefaults(array $context = []) : array
	{
		$design = [
			'WIDTH_BUTTON' => 'AUTO',
		] + $this->designDefaults();

		return $design + ['SELECTOR' => '.goToOrder'] + Guide::getBitrixBasket($context);
	}

	protected function orderDefaults(array $context = []) : array
	{
		return $this->designDefaults() + Guide::getBitrixOrder($context, '/personal/cart/order/');
	}

	public function getDefaults(array $context = []) : array
	{
		return [
			Behavior\Registry::ELEMENT => [
				'desktop' => $this->elementDesktopDefaults($context),
				'mobile' => $this->elementMobileDefaults($context),
			],
			Behavior\Registry::ELEMENT_FAST => $this->elementFastDefaults($context),
			Behavior\Registry::BASKET => $this->basketDefaults($context),
			Behavior\Registry::ORDER => $this->orderDefaults($context),
		];
	}
}