<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use YandexPay\Pay;

Loc::loadMessages(__FILE__);

class yandexpay_pay extends CModule
{
	public $MODULE_ID = 'yandexpay.pay';
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $PARTNER_NAME;
	public $PARTNER_URI;
	public $PAYSYSTEM_NAME = 'yandexpay';

	public function __construct()
	{
		$arModuleVersion = null;

		include __DIR__ . '/version.php';

		if (isset($arModuleVersion) && is_array($arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}

		$this->MODULE_NAME = Loc::getMessage('YANDEX_PAY_MODULE_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('YANDEX_PAY_MODULE_DESCRIPTION');

		$this->PARTNER_NAME = Loc::getMessage('YANDEX_PAY_PARTNER_NAME');
		$this->PARTNER_URI = Loc::getMessage('YANDEX_PAY_PARTNER_URI');
	}

	public function DoInstall()
	{
		global $APPLICATION;

		$result = true;

		try
		{
			$this->checkRequirements();

			Main\ModuleManager::registerModule($this->MODULE_ID);

			if (Main\Loader::includeModule($this->MODULE_ID))
			{
				$this->InstallDB();
				$this->InstallEvents();
				$this->InstallAgents();
				$this->InstallFiles();
				$this->InstallPaySystem();
			}
			else
			{
				throw new Main\SystemException(Loc::getMessage('YANDEX_PAY_MODULE_NOT_REGISTERED'));
			}
		}
		catch (Exception $exception)
		{
			$result = false;
			$APPLICATION->ThrowException($exception->getMessage());
		}

		return $result;
	}

	public function DoUninstall(): void
	{
		global $APPLICATION, $step;

		$step = (int)$step;

		if ($step < 2)
		{
			$title = Loc::getMessage('YANDEX_PAY_UNINSTALL', [
				'#NAME#' => Loc::getMessage('YANDEX_PAY_MODULE_NAME'),
			]);

			$APPLICATION->IncludeAdminFile($title, __DIR__ . '/unstep1.php');
		}
		else if ($step === 2)
		{
			if (Main\Loader::includeModule($this->MODULE_ID))
			{
				$request = Main\Context::getCurrent()->getRequest();

				if ($request->get('savedata') !== 'Y')
				{
					$this->UnInstallDB();
				}

				$this->UnInstallEvents();
				$this->UnInstallAgents();
				$this->UnInstallFiles();
			}

			Main\ModuleManager::unRegisterModule($this->MODULE_ID);
		}
	}

	public function InstallDB()
	{
		$controller = new Pay\Reference\Storage\Controller();
		$controller->createTable();
	}

	public function UnInstallDB(): void
	{
		$controller = new Pay\Reference\Storage\Controller();
		$controller->dropTable();
	}

	public function InstallEvents(): void
	{
		Pay\Reference\Event\Controller::updateRegular();
	}

	public function UnInstallEvents(): void
	{
		Pay\Reference\Event\Controller::deleteAll();
	}

	public function InstallAgents(): void
	{
		Pay\Reference\Agent\Controller::updateRegular();
	}

	public function UnInstallAgents(): void
	{
		Pay\Reference\Agent\Controller::deleteAll();
	}

	public function InstallPaySystem() : void
	{
		Pay\Ui\UseCase\AutoInstallPaySystem::install();
	}

	public function InstallFiles(): void
	{
		$moduleSafe = str_replace('.', '', $this->MODULE_ID);

		CopyDirFiles(__DIR__ . '/admin', $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/admin', true, true);
		CopyDirFiles(__DIR__ . '/components', $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/components/' . $this->MODULE_ID, true, true);
		CopyDirFiles(__DIR__ . '/images', $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/images/sale/sale_payments/', true, true);
		CopyDirFiles(__DIR__ . '/js', $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/js/' . $moduleSafe, true, true);
		CopyDirFiles(__DIR__ . '/handler', $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/php_interface/include/sale_payment/' . $this->PAYSYSTEM_NAME, true, true);
		CopyDirFiles(__DIR__ . '/tools', $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/tools/' . $this->MODULE_ID , true, true);
	}

	public function UnInstallFiles(): void
	{
		$moduleSafe = str_replace('.', '', $this->MODULE_ID);

		DeleteDirFiles(__DIR__ . '/admin', $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/admin');
		DeleteDirFilesEx(BX_ROOT . '/components/' . $this->MODULE_ID);
		DeleteDirFilesEx(BX_ROOT . '/js/' . $moduleSafe);
		DeleteDirFilesEx(BX_ROOT . '/php_interface/include/sale_payment/' . $this->PAYSYSTEM_NAME);
		DeleteDirFilesEx(BX_ROOT . '/tools/' . $this->MODULE_ID);
		unlink($_SERVER['DOCUMENT_ROOT'] . BX_ROOT  . '/images/sale/sale_payments/' . $this->PAYSYSTEM_NAME . '.png');
	}

	protected function checkRequirements(): void
	{
		// require php version

		$requirePhp = '7.2.0';

		if (CheckVersion(PHP_VERSION, $requirePhp) === false)
		{
			throw new Main\SystemException(Loc::getMessage('YANDEX_PAY_INSTALL_REQUIRE_PHP', [ '#VERSION#' => $requirePhp ]));
		}

		// required modules

		$requireModules = [
			'main'  => '19.0.0',
			'sale'  => '18.6'
		];

		if (class_exists(Main\ModuleManager::class))
		{
			foreach ($requireModules as $moduleName => $moduleVersion)
			{
				$currentVersion = Main\ModuleManager::getVersion($moduleName);

				if ($currentVersion !== false && CheckVersion($currentVersion, $moduleVersion))
				{
					unset($requireModules[$moduleName]);
				}
			}
		}

		if (!empty($requireModules))
		{
			$moduleVersion = reset($requireModules);
			$moduleName = key($requireModules);

			throw new Main\SystemException(Loc::getMessage('YANDEX_PAY_INSTALL_REQUIRE_MODULE', [
				'#MODULE#' => $moduleName,
				'#VERSION#' => $moduleVersion
			]));
		}
	}
}
