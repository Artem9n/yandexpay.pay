<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderRender\Stage;

use Bitrix\Main;
use YandexPay\Pay\Data;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action\Rest;
use YandexPay\Pay\Trading\Settings\Options;

class YandexDeliveryCollector extends Rest\Stage\ResponseCollector
{
	/** @var Options\Delivery */
	protected $yandexDelivery;

	public function __construct(Options\Delivery $yandexDelivery, Rest\Reference\EffectiveResponse $response, string $key = '')
	{
		parent::__construct($response, $key);

		$this->yandexDelivery = $yandexDelivery;
	}

	public function __invoke(Rest\State\OrderCalculation $state)
	{
		try
		{
			$contact = $this->contact($state);

			$this->write([
				'contact' => $contact,
				'emergencyContact' => $contact,
				'address' => $this->warehouse(),
			]);

		}
		catch (Main\SystemException $exception)
		{
			trigger_error($exception->getMessage(), E_USER_WARNING);
		}
	}

	protected function contact(Rest\State\OrderCalculation $state) : array
	{
		$userId = $this->yandexDelivery->getUserId();

		Assert::notNull($userId, 'contact[userId]');

		$user = $state->environment->getUserRegistry()->getUser(['ID' => $userId]);
		$useData = $user->getUserData();

		$phone = $useData['PERSONAL_PHONE'] ?: $useData['PERSONAL_MOBILE'] ?: $useData['WORK_PHONE'] ?: '';

		return [
			'firstName' => $useData['NAME'] ?: null,
			'secondName' => $useData['SECOND_NAME'] ?: null,
			'lastName' => $useData['LAST_NAME'] ?: null,
			'phone' => Data\Phone::format($phone),
			'email' => $useData['EMAIL'] ?: null,
		];
	}

	protected function warehouse() : array
	{
		$validate = $this->yandexDelivery->validate();

		if (!$validate->isSuccess())
		{
			throw new Main\SystemException('invalid yandex delivery');
		}

		$warehouse = $this->yandexDelivery->getWarehouse();

		return [
			'country' => $warehouse->getCountry(),
			'locality' => $warehouse->getLocality(),
			'street' => $warehouse->getStreet(),
			'building' => $warehouse->getBuilding(),
			'entrance' => $warehouse->getEntrance(),
			'floor' => $warehouse->getFloor(),
			'location' => [
				'longitude' => $warehouse->getLon(),
				'latitude' => $warehouse->getLat(),
			],
		];
	}
}