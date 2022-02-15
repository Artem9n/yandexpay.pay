<?php
namespace YandexPay\Pay\Injection\Solution;

class Guide
{
	public static function getBitrixOrder(array $context = [], string $path = '/personal/order/make/') : array
	{
		$dir = $context['SITE_DIR'] ?? '';
		$dir = rtrim($dir, '/');

		return [
			'SELECTOR' => '#bx-soa-total .bx-soa-cart-total-ghost, .bx-soa-section',
			'POSITION' => 'beforebegin',
			'PATH' => $dir . $path,
			'WIDTH_BUTTON' => 'MAX'
		];
	}

	public static function getBitrixBasket(array $context = [], string $path = '/personal/cart/') : array
	{
		$dir = $context['SITE_DIR'] ?? '';
		$dir = rtrim($dir, '/');

		return [
			'SELECTOR' => '#basket-root',
			'POSITION' => 'beforebegin',
			'PATH' => $dir . $path,
		];
	}
}