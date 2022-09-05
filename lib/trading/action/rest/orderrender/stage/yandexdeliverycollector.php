<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderRender\Stage;

use Bitrix\Main;
use YandexPay\Pay\Data;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action\Rest;
use YandexPay\Pay\Trading\Settings\Options;
use YandexPay\Pay\Trading\Action\Rest\Dto;

class YandexDeliveryCollector extends Rest\Stage\ResponseCollector
{
	/** @var Options\Delivery */
	protected $yandexDelivery;
	/** @var Dto\Address|null  */
	protected $address;

	public function __construct(
		Options\Delivery $yandexDelivery,
		Rest\Reference\EffectiveResponse $response,
		Dto\Address $address = null,
		string $key = ''
	)
	{
		parent::__construct($response, $key);

		$this->yandexDelivery = $yandexDelivery;
		$this->address = $address;
	}

	public function __invoke(Rest\State\OrderCalculation $state)
	{
		try
		{
			$strategy = $this->yandexDelivery->getCatalogStore();

			if ($strategy === '')
			{
				$contactId = $this->yandexDelivery->getEmergencyContact();
				$warehouse = $this->yandexDelivery->getWarehouse();
			}
			else
			{
				$storeService = $state->environment->getStore();

				$storeWarehouseField = $this->yandexDelivery->getStoreWarehouseField();
				$storeContactField = $this->yandexDelivery->getStoreContactField();

				$storeIds = $storeService->available($state->order);

				if (empty($storeIds)) { return; }

				$storeId = $storeService->expressStrategy($strategy)->resolve($storeIds, $this->address, [
					'WAREHOUSE_FIELD' => $storeWarehouseField,
					'CONTACT_FIELD' => $storeContactField,
				]);

				if ($storeId === null)
				{
					throw new Main\SystemException('not resolve store');
				}

				$contactId = $storeService->contact($storeId, $storeContactField);
				$warehouse = $storeService->warehouse($storeId, $storeWarehouseField);
			}

			$contact = $this->contact($state, $contactId);

			$this->write([
				'contact' => $contact,
				'emergencyContact' => $contact,
				'address' => $this->warehouseAddress($warehouse),
			]);
		}
		catch (Main\SystemException $exception)
		{
			trigger_error($exception->getMessage(), E_USER_WARNING);
		}
	}

	protected function contact(Rest\State\OrderCalculation $state, int $contactId = null) : array
	{
		Assert::notNull($contactId, 'contact[userId]');

		$user = $state->environment->getUserRegistry()->getUser(['ID' => $contactId]);
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

	protected function warehouseAddress(Options\Warehouse $warehouse) : array
	{
		return [
			'country' => $warehouse->getCountry(),
			'locality' => $warehouse->getLocality(),
			'street' => $warehouse->getStreet(),
			'building' => $warehouse->getBuilding(),
			'entrance' => $warehouse->getEntrance(),
			'floor' => $warehouse->getFloor(),
			'addressLine' => $warehouse->getFullAddress(),
			'location' => [
				'longitude' => $warehouse->getLon(),
				'latitude' => $warehouse->getLat(),
			],
		];
	}
}