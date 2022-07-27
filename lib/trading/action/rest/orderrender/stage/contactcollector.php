<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderRender\Stage;

use YandexPay\Pay\Trading\Action\Rest\Reference\EffectiveResponse;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Action\Rest\Stage\ResponseCollector;
use YandexPay\Pay\Trading\Settings\Options;

class ContactCollector extends ResponseCollector
{
	/** @var Options\Delivery */
	protected $yandexDelivery;

	public function __construct(Options\Delivery $yandexDelivery, EffectiveResponse $response, string $key = '')
	{
		$this->yandexDelivery = $yandexDelivery;
		parent::__construct($response, $key);
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$userId = $this->yandexDelivery->getUserId();

		if ($userId === null) { return; }

		$user = $state->environment->getUserRegistry()->getUser(['ID' => $userId]);
		$useData = $user->getUserData();

		$formatData = [
			'firstName' => $useData['NAME'] ?: null,
			'secondName' => $useData['SECOND_NAME'] ?: null,
			'lastName' => $useData['LAST_NAME'] ?: null,
			'phone' => $useData['PERSONAL_MOBILE'] ?: $useData['PERSONAL_PHONE'] ?: $useData['WORK_PHONE'] ?: null,
			'email' => $useData['EMAIL'] ?: null,
		];

		$this->write([
			'contact' => $formatData,
			'emergencyContact' => $formatData,
		]);
	}
}