<?php
namespace YandexPay\Pay\Trading\Action\Rest;

use YandexPay\Pay\Reference\Assert;

class Router
{
	public function getAction($path) : Reference\HttpAction
	{
		$map = $this->actionMap();

		if (!isset($map[$path])) { throw new Exceptions\ActionNotImplemented(); }

		$className = $map[$path];

		Assert::isSubclassOf($className, Reference\HttpAction::class);

		return new $className();
	}

	/** @return array<string, class-string<Reference\HttpAction>> */
	protected function actionMap() : array
	{
		return [
			'root' => Root\Action::class,
			'v1/order/render' => OrderRender\Action::class,
			'v1/pickup-options' => PickupOptions\Action::class,
			'v1/pickup-option-details' => PickupDetail\Action::class,
			'v1/order/create' => OrderCreate\Action::class,
			'v1/webhook' => OrderWebhook\Action::class,
			'v1/onboard' => Onboard\Action::class,
			'authorize' => Authorize\Action::class,
			'button/data' => ButtonData\Action::class,
			'onboard/ping' => OnboardPing\Action::class,
		];
	}
}