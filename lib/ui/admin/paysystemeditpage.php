<?php

namespace YandexPay\Pay\Ui\Admin;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Gateway\Manager;

class PaySystemEditPage
{
	public static function isTarget() : bool
	{
		$request = Main\Context::getCurrent()->getRequest();

		return $request->getRequestedPage() === Path::getPageUrl('sale_pay_system_edit')
			|| $request->getRequestedPage() === Path::getPageUrl('sale_pay_system_ajax');
	}

	public static function selectedGateway() : ?array
	{
		$result = [];

		$gateway = null;
		$request = Main\Context::getCurrent()->getRequest();
		$paySystemId = $request->get('ID') ?? $request->get('paySystemId');

		if ($paySystemId !== null)
		{
			$query = Sale\Internals\PaySystemActionTable::getList([
				'filter' => [
					'=ID' => $paySystemId,
					'!PS_MODE' => false
				],
				'select' => ['ID', 'PS_MODE', 'ACTION_FILE'],
				'limit' => 1
			]);

			if (($system = $query->fetch()))
			{
				$gateway = $system['PS_MODE'];
			}
		}
		else if ($request->get('PS_MODE') !== null)
		{
			$gateway = $request->get('PS_MODE');
		}

		if ($gateway !== null)
		{
			$provider = Manager::getProvider($gateway);

			$result[$provider->getId()] = $provider->getName();
		}

		return $result;
	}

	public static function validateMerchantButton() : bool
	{
		$result = true;
		$gateway = null;

		$request = Main\Context::getCurrent()->getRequest();
		$paySystemId = $request->get('ID') ?? $request->get('paySystemId');

		if ($request->get('PS_MODE') !== null)
		{
			$gateway = $request->get('PS_MODE');
		}
		else if ($paySystemId !== null)
		{
			$query = Sale\Internals\PaySystemActionTable::getList([
				'filter' => [
					'=ID' => $paySystemId,
					'!PS_MODE' => false
				],
				'select' => ['ID', 'PS_MODE', 'ACTION_FILE'],
				'limit' => 1
			]);

			if (($system = $query->fetch()))
			{
				$gateway = $system['PS_MODE'];
			}
		}

		if ($gateway === Manager::PAYTURE || $gateway === Manager::RBS_ALFA)
		{
			$result = false;
		}

		return $result;
	}
}