<?php
namespace YandexPay\Pay\Ui\Update\Check;

use Bitrix\Main;
use YandexPay\Pay;
use YandexPay\Pay\Reference\Concerns;

class Agent extends Pay\Reference\Agent\Regular
{
	use Concerns\HasMessage;

	public const NOTIFY_TAG = 'YANDEX_PAY_UPDATE_CHECK';

	public static function getDefaultParams() : array
	{
		return [
			'interval' => 3600 * 24 * 7,
		];
	}

	public static function run() : void
	{
		$moduleName = Pay\Config::getModuleName();
		$currentVersion = static::getCurrentVersion($moduleName);
		$newVersion = static::getNewVersion($moduleName);

		if ($newVersion === null) { return; }

		if (static::compareVersion($newVersion, $currentVersion) > 0)
		{
			static::addNotify($moduleName, $newVersion);
		}
	}

	protected static function addNotify(string $moduleName, string $version) : void
	{
		if (Pay\Config::getOption('ui_update_check_agent_last_version') !== $version)
		{
			Pay\Config::setOption('ui_update_check_agent_last_version', $version);

			$message = self::getMessage('NOTIFY', [
				'#MODULE_NAME#' => $moduleName,
				'#VERSION#' => $version,
				'#LINK#' => '/bitrix/admin/update_system_partner.php?' . http_build_query([
						'tabControl_active_tab' => 'tab2',
						'addmodule' => $moduleName,
						'lang' => LANGUAGE_ID
					])
			]);

			\CAdminNotify::Add([
				'MESSAGE' => $message,
				'MODULE_ID' => $moduleName,
				'TAG' => static::NOTIFY_TAG
			]);

			Event::install();
		}
	}

	protected static function compareVersion(string $versionA, string $versionB) : int
	{
		$versionAParts = explode('.', $versionA);
		$versionBParts = explode('.', $versionB);
		$result = 0;

		foreach ($versionAParts as $index => $partA)
		{
			$partAInteger = (int)$partA;
			$partB = $versionBParts[$index] ?? null;
			$partBInteger = (int)$partB;

			if ($partAInteger < $partBInteger)
			{
				$result = -1;
				break;
			}

			if ($partAInteger > $partBInteger)
			{
				$result = 1;
				break;
			}
		}

		return $result;
	}

	protected static function getCurrentVersion(string $moduleName)
	{
		return Main\ModuleManager::getVersion($moduleName);
	}

	protected static function getNewVersion($moduleName)
	{
		$result = null;

		if (static::loadUpdater())
		{
			$errorMessage = '';

			$updateList = \CUpdateClientPartner::GetUpdatesList($errorMessage, false, 'Y', [ $moduleName ]);

			if (!empty($updateList['MODULE']) && is_array($updateList['MODULE']))
			{
				foreach ($updateList['MODULE'] as $update)
				{
					$isTargetModule = (isset($update['@']['ID']) && $update['@']['ID'] === $moduleName);
					$hasNewVersion = (isset($update['#']['VERSION']) && is_array($update['#']['VERSION']));

					if ($isTargetModule && $hasNewVersion)
					{
						foreach ($update['#']['VERSION'] as $updateVersion)
						{
							if (isset($updateVersion['@']['ID']))
							{
								$result = $updateVersion['@']['ID'];
							}
						}

						break;
					}
				}
			}
		}

		return $result;
	}

	protected static function loadUpdater() : bool
	{
		$path = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/update_client_partner.php';
		$result = false;

		if (file_exists($path))
		{
			require_once $path;
			$result = class_exists(\CUpdateClientPartner::class);
		}

		return $result;
	}
}
