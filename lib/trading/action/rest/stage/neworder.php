<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use Bitrix\Main;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Exceptions;

class NewOrder
{
	protected $items;
	protected $userId;
	protected $fUserId;
	protected $currency;
	protected $coupons;

	public function __construct(int $userId = null, int $fUserId = null, string $currency = null, array $coupons = null)
	{
		$this->userId = $userId;
		$this->fUserId = $fUserId;
		$this->currency = $currency;
		$this->coupons = $coupons;
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$this->normalizeAsproRegion($state);

		$this->makeOrder($state);
		$this->fillPersonType($state);
		$this->fillCoupons($state);
		$this->fillTradingPlatform($state);
	}

	protected function makeOrder(State\OrderCalculation $state) : void
	{
		$state->order = $state->environment->getOrderRegistry()->createOrder(
			$state->setup->getSiteId(),
			$state->userId ?? $this->userId, // todo only userId
			$this->currency
		);

		$state->order->setFUserId($state->fUserId ?? $this->fUserId);
	}

	protected function fillPersonType(State\OrderCalculation $state) : void
	{
		$personTypeResult = $state->order->setPersonType($state->setup->getPersonTypeId());

		Exceptions\Facade::handleResult($personTypeResult);
	}

	protected function fillCoupons(State\OrderCalculation $state) : void
	{
		if ($this->coupons === null) { return; }

		foreach ($this->coupons as $coupon)
		{
			$value = $coupon['value'];

			if ($value === null || trim($value) === '') { continue; }

			$state->order->applyCoupon($value);
		}
	}

	protected function fillTradingPlatform(State\OrderCalculation $state) : void
	{
		$platform = $state->environment->getPlatform();
		$state->order->fillTradingSetup($platform);
	}

	protected function normalizeAsproRegion(State\OrderCalculation $state) : void
	{
		global $arRegion;

		if (empty($arRegion['LIST_PRICES']) || !is_array($arRegion['LIST_PRICES'])) { return; }
		if (!Main\Loader::includeModule('catalog')) { return; }

		$userId = (int)($state->userId ?? $this->userId);
		$userGroups = Main\UserTable::getUserGroupIds($userId);
		$permissions = \CCatalogGroup::GetGroupsPerms($userGroups);
		$buyPermissions = array_flip($permissions['buy']);

		foreach ($arRegion['LIST_PRICES'] as &$priceType)
		{
			if (!isset($priceType['ID'])) { continue; }

			$priceType['CAN_BUY'] = isset($buyPermissions[$priceType['ID']]) ? 'Y' : 'N';
		}
		unset($priceType);
	}
}

