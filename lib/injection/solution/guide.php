<?php
namespace YandexPay\Pay\Injection\Solution;

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
			'WIDTH_BUTTON' => 'MAX'
		];
	}

	public static function getBitrixBasket(array $context = [], string $path = '/personal/cart/') : array
	{
		$dir = $context['SITE_DIR'] ?? '';
		$dir = rtrim($dir, '/');

		return [
			'SELECTOR' => '.basket-checkout-block .fastorder, .basket-checkout-block.basket-checkout-block-btn',
			'POSITION' => 'afterend',
			'PATH' => $dir . $path,
			'WIDTH_BUTTON' => 'MAX'
		];
	}

	public static function getBitrixOrderPath(array $context = [], string $path = '/personal/order/make/') : string
	{
		$dir = $context['SITE_DIR'] ?? '';
		$dir = rtrim($dir, '/');

		return $dir . $path;
	}
}