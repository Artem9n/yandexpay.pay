<?php
namespace YandexPay\Pay\Ui\Trading;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay;

class SetupTabGrid extends SetupGrid
{
	use Concerns\HasMessage;

	protected $backUrl;
	protected $isTarget = false;

	protected function getShowParameters() : array
	{
		$common = parent::getShowParameters();
		$local = [
			'TITLE' => null,
			'GRID_ID' => $common['GRID_ID'] . '_TAB',
			'SUBLIST' => 'Y',
			'SUBLIST_TARGET' => $this->isTarget ? 'Y' : 'N',
			'AJAX_URL' => Pay\Ui\Admin\Path::getModuleUrl('trading_tab_grid', $this->getBaseQuery()),
			'CONTEXT_MENU' => array_map(static function(array $item) {
				if ($item['ICON'] === 'btn_new') { $item['ICON'] = 'btn_sub_new'; }
				return $item;
			}, $common['CONTEXT_MENU']),
		];

		return $local + $common;
	}

	protected function getBaseQuery() : array
	{
		$result = parent::getBaseQuery();

		if ($this->backUrl !== null)
		{
			$result['BACKURL'] = $this->backUrl;
		}

		return $result;
	}

	public function markTarget() : void
	{
		$this->isTarget = true;
	}

	public function getBackUrl()
	{
		return $this->backUrl;
	}

	public function setBackUrl(string $backUrl) : void
	{
		$this->backUrl = $backUrl;
	}
}