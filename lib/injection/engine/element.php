<?php
namespace YandexPay\Pay\Injection\Engine;

use Bitrix\Main;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\IblockTable;
use YandexPay\Pay\Injection;
use YandexPay\Pay\Reference\Event;

class Element extends Event\Base
{
	public static function onEpilog(int $injectionId, array $settings) : void
	{
		self::render($injectionId, $settings);
	}

	protected static function render(int $injectionId, array $settings) : void
	{
		if (self::$handlerDisallowYaPay) { return; }

		global $APPLICATION;

		$model = Injection\Setup\Model::wakeUp(['ID' => $injectionId]);

		if (trim($model->getSelectorValue()) === '') { return; }

		$iblockId = $settings['IBLOCK'];

		$url = static::getUrl();
		$element = static::getDetailPageUtlTemplate($iblockId, $url);

		if (empty($element)) { return; }

		$elementId = static::getElementId($iblockId, $element);

		if ($elementId === null) { return; }

		$APPLICATION->IncludeComponent('yandexpay.pay:trading.cart', '', [
			'INJECTION_ID'	=> $injectionId,
			'PRODUCT_ID'	=> $elementId,
			'MODE'			=> Injection\Behavior\Registry::ELEMENT
		], false);

		self::$handlerDisallowYaPay = true;
	}

	protected static function getUrl() : string
	{
		return Main\Context::getCurrent()->getRequest()->getRequestedPage();
	}

	protected static function getDetailPageUtlTemplate(int $iblockId, string $url) : ?array
	{
		if (!Main\Loader::includeModule('iblock')) { return null; }

		$result = null;
		$variables = [];

		$engine = new \CComponentEngine();

		$query = IblockTable::getList([
			'filter' => [ '=ID' => $iblockId ],
			//'cache' => [ 'ttl' => 360000 ], //todo, not clear
		]);

		$iblock = $query->fetchObject();

		if (!$iblock) { return null; }

		$result['SEF_FOLDER'] = mb_substr($iblock->getListPageUrl(), mb_strlen('#SITE_DIR#'));
		$result['SEF_URL_TEMPLATES']['element'] = mb_substr($iblock->getDetailPageUrl(), mb_strlen($iblock->getListPageUrl()));

		$engine->guessComponentPath(
			$result['SEF_FOLDER'],
			$result['SEF_URL_TEMPLATES'],
			$variables,
			$url
		);

		if (!empty($variables)) { return $variables; }

		$componentVariables = [
			'SECTION_ID',
			'SECTION_CODE',
			'ELEMENT_ID',
			'ELEMENT_CODE'
		];

		\CComponentEngine::initComponentVariables(false, $componentVariables, [], $variables);

		return $variables;
	}

	protected static function getElementId(int $iblockId, array $element) : ?int
	{
		$filter = ['ACTIVE' => 'Y', 'IBLOCK_ID' => $iblockId];

		if (isset($element['ELEMENT_CODE']))
		{
			$filter['=CODE'] = $element['ELEMENT_CODE'];
		}

		if (isset($element['ELEMENT_ID']))
		{
			$filter['=ID'] = $element['ELEMENT_ID'];
		}

		$query = ElementTable::getList([
			'filter' => $filter,
			'select' => ['ID']
		]);

		$row = $query->fetchObject();

		if (!$row) { return null; }

		return $row->getId();
	}
}