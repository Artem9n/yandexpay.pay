<?php
namespace YandexPay\Pay\Ui\Trading;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay;

class LogTabGrid extends Pay\Ui\Reference\Page
{
	use Concerns\HasMessage;

	public function show() : void
	{
		global $APPLICATION;

		$APPLICATION->IncludeComponent(
			'yandexpay.pay:admin.grid',
			'',
			$this->getShowParameters()
		);
	}

	protected function getShowParameters() : array
	{
		return [
			'TITLE' => null,
			'GRID_ID' => 'YANDEX_PAY_LOG_GRID_TAB',
			'ALLOW_SAVE' => Pay\Ui\Access::hasRights($this->getReadRights()),
			'PROVIDER_CLASS_NAME' => Pay\Component\Log\Grid::class,
			'AJAX_URL' => Pay\Ui\Admin\Path::getModuleUrl('log_tab_grid', $this->getBaseQuery()),
			'DATA_CLASS_NAME' => Pay\Logger\Table::class,
			'SUBLIST' => 'Y',
			'SUBLIST_TARGET' => 'Y',
			'LIST_FIELDS' => [
				'TIMESTAMP_X',
				'LEVEL',
				'AUDIT',
				'MESSAGE',
				'URL',
				'TRACE',
			],
		];
	}

	protected function getBaseQuery() : array
	{
		return [
			'lang' => LANGUAGE_ID,
		];
	}
}