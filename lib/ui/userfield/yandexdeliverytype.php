<?php

namespace YandexPay\Pay\Ui\UserField;

use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading;

class YandexDeliveryType
{
	use Concerns\HasMessage;

	public static function getAdminListViewHTML($userField, $htmlControl)
	{
		try
		{
			if (empty($userField['ENTITY_VALUE_ID'])) { return ''; }

			$setup = Trading\Setup\Model::wakeUp(['ID' => $userField['ENTITY_VALUE_ID']]);

			$options = $setup->wakeupOptions();
			$result = $options->getDeliveryOptions()->getYandexDelivery();

			if ($result !== null)
			{
				$validateResult = $result->validate();

				if ($validateResult->isSuccess())
				{
					$message = static::getMessage('READY');
				}
				else
				{
					$message = static::getMessage('INVALID', [
						'#MESSAGE#' => implode(PHP_EOL, $validateResult->getErrorMessages()),
					]);
				}
			}
			else
			{
				$query = [
					'ID' => $options->getPaymentCard(),
					'lang' => LANGUAGE_ID,
					'yapayAction' => 'installYandexDelivery',
					'setupId' => $userField['ENTITY_VALUE_ID'],
				];

				$path = BX_ROOT . '/admin/sale_pay_system_edit.php?' . http_build_query($query);

				$message = static::getMessage('NOT_INSTALLED', [
					'#ACTIVATE_LINK#' => $path,
				]);
			}
		}
		catch (Main\SystemException $exception)
		{
			$message = static::getMessage(
				'ERROR',
				[ '#MESSAGE#' => $exception->getMessage() ],
				$exception->getMessage()
			);
		}

		return $message;
	}
}