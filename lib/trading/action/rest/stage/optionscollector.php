<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use YandexPay\Pay\Trading\Action\Rest\State;

class OptionsCollector extends ResponseCollector
{
	public function __invoke(State\OrderCalculation $state)
	{
		$this->availablePaymentMethods($state);
		$this->availableShippingMethods($state);
		$this->enableCoupons($state);
		$this->enableComment($state);
		$this->requiredFields($state);
	}

	protected function availablePaymentMethods(State\OrderCalculation $state) : void
	{
		$result = array_flip(array_filter([
			'CARD' => $state->options->getPaymentCard(),
			'CASH_ON_DELIVERY' => $state->options->getPaymentCash(),
		]));

		$result[] = 'SPLIT';

		$this->write(array_values($result), 'availablePaymentMethods');
	}

	protected function availableShippingMethods(State\OrderCalculation $state) : void
	{
		$result = ['COURIER', 'PICKUP'];

		if ($this->response->getField('shipping.yandexDelivery') !== null)
		{
			$result[] = 'YANDEX_DELIVERY';
		}

		$this->write($result, 'shipping.availableMethods');// todo get type shipping courier and pickup
	}

	protected function enableCoupons(State\OrderCalculation $state) : void
	{
		$this->write($state->options->useCoupons(), 'enableCoupons');
	}

	protected function enableComment(State\OrderCalculation $state) : void
	{
		$this->write($state->options->useComment(), 'enableCommentField');
	}

	protected function requiredFields(State\OrderCalculation $state) : void
	{
		$result = [
			'billingContact' => [
				'name' => $state->options->useBuyerName(),
				'email' => $state->options->useBuyerEmail(),
				'phone' => $state->options->useBuyerPhone(),
			],
			'shippingContact' => [
				'name' => $state->options->useBuyerName(),
				'email' => $state->options->useBuyerEmail(),
				'phone' => $state->options->useBuyerPhone(),
			],
		];

		$this->write($result, 'requiredFields');
	}
}

