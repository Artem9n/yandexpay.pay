<?php
namespace YandexPay\Pay\Ui\Reference;

use Bitrix\Main;
use YandexPay\Pay\Ui;
use YandexPay\Pay\Reference\Concerns;

abstract class Page
{
	use Concerns\HasMessage;
	
	protected $request;
	protected $warnings = [];

	public function __construct(Main\HttpRequest $request = null)
	{
		$this->request = $request ?? Main\Context::getCurrent()->getRequest();
	}

	abstract public function show() : void;

	public function checkSession() : void
	{
		if (!check_bitrix_sessid())
		{
			throw new Main\SystemException(self::getMessage('SESSION_EXPIRED'));
		}
	}

	public function checkReadAccess() : void
	{
		$rights = $this->getReadRights();

		if (!Ui\Access::hasRights($rights))
		{
			throw new Main\AccessDeniedException(self::getMessage('READ_ACCESS_DENIED'));
		}
	}

	protected function getReadRights() : string
	{
		return Ui\Access::RIGHTS_READ;
	}

	public function checkWriteAccess() : void
	{
		$rights = $this->getWriteRights();

		if (!Ui\Access::hasRights($rights))
		{
			throw new Main\AccessDeniedException(self::getMessage('WRITE_ACCESS_DENIED'));
		}
	}

	protected function getWriteRights() : string
	{
		return Ui\Access::RIGHTS_WRITE;
	}

	public function loadModules() : void
	{
		$modules = $this->getRequiredModules();

		foreach ($modules as $module)
		{
			if (Main\Loader::includeModule($module)) { continue; }

			throw new Main\SystemException(self::getMessage('REQUIRE_MODULE', [
				'#MODULE#' => $module,
			]));
		}
	}

	public function getRequiredModules() : array
	{
		return [];
	}

	public function refreshPage() : void
	{
		global $APPLICATION;

		$url = $APPLICATION->GetCurPageParam('', [ 'action', 'sessid' ]);

		LocalRedirect($url);
	}

	public function addWarning(string $message) : void
	{
		$this->warnings[] = $message;
	}

	public function hasWarnings() : bool
	{
		return !empty($this->warnings);
	}

	public function showWarnings() : void
	{
		\CAdminMessage::ShowMessage([
			'TYPE' => 'ERROR',
			'MESSAGE' => implode('<br />', $this->warnings),
			'HTML' => true
		]);
	}
}