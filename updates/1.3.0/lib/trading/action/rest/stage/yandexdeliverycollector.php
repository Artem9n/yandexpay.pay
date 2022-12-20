<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

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
				$schedule = $this->yandexDelivery->getShipmentSchedule();
			}
			else
			{
				$storeService = $state->environment->getStore();

				$storeWarehouseField = $this->yandexDelivery->getStoreWarehouseField();
				$storeContactField = $this->yandexDelivery->getStoreContactField();
				$storeScheduleField = $this->yandexDelivery->getStoreShipmentSchedule();

				$storeIds = $storeService->available($state->order);

				if (empty($storeIds)) { return; }

				$strategyService = $storeService->expressStrategy($strategy);

				$store = $strategyService->resolve($storeIds, $this->address, [
					'WAREHOUSE_FIELD' => $storeWarehouseField,
					'CONTACT_FIELD' => $storeContactField,
					'SHIPMENT_SCHEDULE_FIELD' => $storeScheduleField,
					'SITE_ID' => $state->setup->getSiteId(),
				]);

				if ($store === null)
				{
					throw new Main\SystemException('not resolve store');
				}

				$contactId = $storeService->contact($store, $storeContactField);
				$warehouse = $storeService->warehouse($store, $storeWarehouseField);
				$schedule = $storeService->schedule($store, $storeScheduleField);
			}

			$contact = $this->contact($state, $contactId);

			$this->write([
				'contact' => $contact,
				'emergencyContact' => $contact,
				'address' => $this->warehouseAddress($warehouse),
				'schedule' => $this->scheduleCollector($schedule),
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

	protected function scheduleCollector(Options\ShipmentSchedule $schedule) : array
	{
		$weekly = $this->weeklyCollector($schedule->getSchedule());
		$custom = $this->customCollector($schedule->getHoliday());

		return [
			'tzoffset' => $schedule->getTimeZoneOffset(),
			'weekly' => $weekly,
			'custom' => $custom,
		];
	}

	protected function weeklyCollector(Options\ScheduleOptions $options) : array
	{
		$result = [];
		$weekdays = [
			1 => 'mon',
			2 => 'tue',
			3 => 'wed',
			4 => 'thu',
			5 => 'fri',
			6 => 'sat',
			7 => 'sun',
		];

		for ($i = 1; $i <= 7; ++$i)
		{
			$workTime = null;

			/** @var Options\ScheduleOption $workday */
			foreach ($options as $workday)
			{
				if ($workday->isMatchWeekday($i))
				{
					$startTime = $workday->getFromTime();
					$endTime = $workday->getToTime();

					if ($startTime !== null && $endTime !== null)
					{
						$workTime = [
							'start' => $workday->getFromTime(),
							'end' => $workday->getToTime(),
						];
					}
					break;
				}
			}

			$day = $weekdays[$i];
			$result[$day] = $workTime;
		}

		return $result;
	}

	protected function customCollector(Options\HolidayOption $holiday) : ?array
	{
		$result = null;
		$calendar = $holiday->getCalendar();
		$holidays = array_flip($calendar->holidays());
		$workdays = array_flip($calendar->workdays());
		$holidayWorktime = $holiday->getIntervals();
		$iteratorDate = new Main\Type\Date();

		for ($i = 0; $i < 30; ++$i)
		{
			$day = $iteratorDate->format('d.m');

			if (isset($workdays[$day]))
			{
				$startTime = $holidayWorktime->getFromTime();
				$endTime = $holidayWorktime->getToTime();
				$workTime = null;
				if ($startTime !== null && $endTime !== null)
				{
					$workTime = [
						'start' => $holidayWorktime->getFromTime(),
						'end' => $holidayWorktime->getToTime(),
					];
				}
			}
			else if (isset($holidays[$day]))
			{
				$workTime = null;
			}
			else
			{
				$iteratorDate->add('P1D');
				continue;
			}

			$result[$iteratorDate->format('Y-m-d')] = $workTime;

			$iteratorDate->add('P1D');
		}

		return $result;
	}
}