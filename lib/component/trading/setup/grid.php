<?php

namespace YandexPay\Pay\Component\Trading\Setup;

use Bitrix\Main;
use Bitrix\Catalog;
use YandexPay\Pay\Component;
use YandexPay\Pay\Trading;
use YandexPay\Pay\Ui\UseCase\AutoInstallInjection;

class Grid extends Component\Model\Grid
{
	public function processPostAction(string $action, array $data) : void
	{
		if ($action !== 'installYandexDelivery'){ return; }

		$request = Main\Context::getCurrent()->getRequest();
		$setupId = $request->get('setupId');

		if ($setupId === null) { return; }

		$setup = Trading\Setup\Model::wakeUp(['ID' => $setupId]);
		$setup->fillPersonTypeId();
		$setup->fillSiteId();
		$setup->fillSettings();
		$setup->wakeupOptions();

		$fields = AutoInstallInjection::getSettingsFields($setup);
		$values = AutoInstallInjection::collectDefaultSettings($fields, 'DELIVERY_OPTIONS');

		$setup->syncSettings($values);
		$setup->getSettings()->save(true);

		$uri = new Main\Web\Uri($request->getRequestUri());
		$uri->deleteParams(['yapayAction', 'setupId', 'BACKURL']);
		LocalRedirect($uri->getUri());
	}
}