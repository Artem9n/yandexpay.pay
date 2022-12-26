<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use YandexPay\Pay\Trading\Action\Rest\State;

class OptionsCollector extends ResponseCollector
{
	public function __invoke(State\OrderCalculation $state)
	{
		$this->availablePaymentMethods($state);
		$this->availableShippingMethods();
		$this->enableCoupons($state);
		$this->enableComment($state);
		$this->requiredFields($state);
	}

	protected function availablePaymentMethods(State\OrderCalculation $state) : void
	{
		$result = array_flip(array_filter([
			'CARD' => $state->options->getPaymentCard(),
			'CASH_ON_DELIVERY' => $state->options->getPaymentCash(),
			'SPLIT' => $state->options->getPaymentSplit(),
		]));

		$this->write(array_values($result), 'availablePaymentMethods');
	}

	protected function availableShippingMethods() : void
	{
		$methods = $this->response->getField('shipping.availableMethods');

		if (!is_array($methods))
		{
			$this->write([], 'shipping.availableMethods');
		}
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

