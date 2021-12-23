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
			'CONTEXT_MENU' => $this->extendContextMenu($common['CONTEXT_MENU']),
			'ROW_ACTIONS' => $this->extendRowActions($common['ROW_ACTIONS']),
			'RELOAD_EVENTS' => [
				'yapayFormSave',
			],
		];

		return array_diff_key($local + $common, [
			'EDIT_URL' => true,
		]);
	}

	protected function extendContextMenu(array $actions) : array
	{
		foreach ($actions as &$action)
		{
			if (!isset($action['TYPE'])) { continue; }

			if ($action['TYPE'] === 'ADD')
			{
				$action['ICON'] = 'btn_sub_new';
				$action['LINK'] .= '&' . http_build_query(['view' => 'dialog']);
				$action['MODAL_FORM'] = 'Y';
				$action['MODAL_PARAMETERS'] = [
					'width' => 450,
					'height' => 250,
				];
			}
		}
		unset($action);

		return $actions;
	}

	protected function extendRowActions(array $actions) : array
	{
		foreach ($actions as $key => &$action)
		{
			if ($key === 'SETUP')
			{
				$action['URL'] .= '&' . http_build_query(['view' => 'dialog']);
				$action['MODAL_FORM'] = 'Y';
				$action['MODAL_PARAMETERS'] = [
					'width' => 800,
					'height' => 600,
				];
			}
			else if ($key === 'EDIT')
			{
				$action['URL'] .= '&' . http_build_query(['view' => 'dialog']);
				$action['MODAL_FORM'] = 'Y';
				$action['MODAL_PARAMETERS'] = [
					'width' => 450,
					'height' => 250,
				];
			}
		}
		unset($action);

		return $actions;
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