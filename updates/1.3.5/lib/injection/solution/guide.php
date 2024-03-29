<?php
namespace YandexPay\Pay\Injection\Solution;

use YandexPay\Pay\Injection\Behavior;

class Guide
{
	public static function getBitrixOrder(array $context = [], string $path = '/personal/order/make/') : array
	{
		$dir = $context['SITE_DIR'] ?? '';
		$dir = rtrim($dir, '/');

		return [
			'SELECTOR' => '.bx-soa-cart-total',
			'POSITION' => 'beforeend',
			'PATH' => $dir . $path,
			'DISPLAY' => Behavior\Display\Registry::BUTTON,
			'WIDTH_BUTTON' => 'MAX',
		];
	}

	public static function getBitrixBasket(array $context = [], string $path = '/personal/cart/') : array
	{
		$dir = $context['SITE_DIR'] ?? '';
		$dir = rtrim($dir, '/');

		return [
			'SELECTOR' => '.basket-checkout-block-btns-wrap, .basket-checkout-section-inner',
			'POSITION' => 'afterend',
			'PATH' => $dir . $path,
			'DISPLAY' => Behavior\Display\Registry::BUTTON,
			'WIDTH_BUTTON' => 'AUTO',
		];
	}

	public static function getBitrixOrderPath(array $context = [], string $path = '/personal/order/make/') : string
	{
		$dir = $context['SITE_DIR'] ?? '';
		$dir = rtrim($dir, '/');

		return $dir . $path;
	}
}