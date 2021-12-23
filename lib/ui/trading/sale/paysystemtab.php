<?php
namespace YandexPay\Pay\Ui\Trading\Sale;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay;

class PaySystemTab extends Pay\Reference\Event\Regular
{
	use Pay\Reference\Concerns\HasMessage;

	public static function getHandlers() : array
	{
		return [
			[
				'module' => 'main',
				'event' => 'OnAdminTabControlBegin',
			],
		];
	}

	public static function OnAdminTabControlBegin(\CAdminTabControl $tabControl) : void
	{
		$request = Main\Context::getCurrent()->getRequest();

		if (!static::testUrl($request)) { return; }

		$paySystemId = static::getPaySystemId($request);

		if (!static::testPaySystem($paySystemId)) { return; }

		array_splice($tabControl->tabs, 1, 0, [
			[
				'DIV' => 'yapay_trading',
				'TAB' => self::getMessage('TITLE'),
				'TITLE' => self::getMessage('TITLE'),
				'CONTENT' => sprintf(
					'<tr><td id="yapay-paysystem-trading-container">%s</td></tr>',
					static::renderTradingList($paySystemId)
				),
			]
		]);
	}

	protected static function testUrl(Main\HttpRequest $request) : bool
	{
		$path = $request->getRequestedPage();
		$path = mb_strtolower($path);

		return ($path === '/bitrix/admin/sale_pay_system_edit.php');
	}

	protected static function getPaySystemId(Main\HttpRequest $request) : ?int
	{
		$id = (int)$request->get('ID');

		return $id > 0 ? $id : null;
	}

	protected static function testPaySystem(?int $paySystemId) : bool
	{
		if (!Main\Loader::includeModule('sale')) { return false; }

		$paySystem = Sale\PaySystem\Manager::getById($paySystemId);

		if (!$paySystem) { return false; }

		return ($paySystem['ACTION_FILE'] === 'yandexpay');
	}

	protected static function renderTradingList(int $paySystemId) : string
	{
		global $APPLICATION;

		ob_start();

		try
		{
			$controller = new Pay\Ui\Trading\SetupTabGrid();

			$controller->setBackUrl($APPLICATION->GetCurPageParam(''));
			$controller->checkReadAccess();
			$controller->loadModules();

			$controller->show();
		}
		catch (Main\SystemException $exception)
		{
			\CAdminMessage::ShowMessage($exception->getMessage());
		}

		return ob_get_clean();
	}
}