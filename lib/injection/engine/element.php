<?php
namespace YandexPay\Pay\Injection\Engine;

use Bitrix\Main;
use Bitrix\Iblock\ElementTable;
use YandexPay\Pay\Injection;
use YandexPay\Pay\Reference\Assert;

class Element extends AbstractEngine
{
	public static function onEpilog(int $injectionId, array $settings) : void
	{
		if (self::$handlerDisallowYaPay) { return; }

		$url = static::getUrl();

		$element = static::getDetailPageUtlTemplate($settings['IBLOCK'], $url);

		if (empty($element)) { return; }

		$elementId = static::getElementId($settings['IBLOCK'], $element);

		if ($elementId === null) { return; }

		static::render($injectionId, ['PRODUCT_ID' => $elementId]);

		self::$handlerDisallowYaPay = true;
	}

	protected static function getUrl() : string
	{
		return Main\Context::getCurrent()->getRequest()->getRequestedPage();
	}

	protected static function getDetailPageUtlTemplate(int $iblockId, string $url) : ?array
	{
		if (!Main\Loader::includeModule('iblock')) { return null; }

		$result = [];
		$variables = [];

		$listPageUrl = \CIBlock::GetArrayByID($iblockId, 'LIST_PAGE_URL');
		$detailPageUrl = \CIBlock::GetArrayByID($iblockId, 'DETAIL_PAGE_URL');

		if (!$listPageUrl || !$detailPageUrl) { return null; }

		$result['SEF_FOLDER'] = mb_substr($listPageUrl, mb_strlen('#SITE_DIR#'));
		$result['SEF_URL_TEMPLATES']['element'] = mb_substr($detailPageUrl, mb_strlen($listPageUrl));

		$engine = new \CComponentEngine();

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