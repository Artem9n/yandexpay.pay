<?php
namespace YandexPay\Pay\Injection\Engine;

use Bitrix\Main;
use Bitrix\Iblock;
use YandexPay\Pay\Injection;
use YandexPay\Pay\Trading\Entity as TradingEntity;

class Element extends AbstractEngine
{
	protected static $environment;

	public static function onEpilog(int $injectionId, array $settings) : void
	{
		$url = static::getUrl();

		if (!isset($settings['IBLOCK'])) { return; }

		$variables = static::getDetailPageUrlTemplate($settings['IBLOCK'], $url);

		if (empty($variables)) { return; }

		$elementFilter = static::elementFilter($settings['IBLOCK'], $variables);
		$elementId = static::getElementId($elementFilter);

		if ($elementId === null) { return; }

		if (static::isSku($elementId))
		{
			$offerFilter = static::offerFilter($settings['IBLOCK']);
			$offerId = static::searchOffer($settings['IBLOCK'], $elementId, $offerFilter);

			if ($offerId !== null)
			{
				$elementId = $offerId;
			}
		}

		static::render($injectionId, ['PRODUCT_ID' => $elementId]);
	}

	protected static function getUrl() : string
	{
		$request = static::getRequest();

		return $request->getRequestedPage();
	}

	protected static function getDetailPageUrlTemplate(int $iblockId, string $url) : ?array
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
			'ELEMENT_CODE',
		];

		\CComponentEngine::initComponentVariables(false, $componentVariables, [], $variables);

		return $variables;
	}

	protected static function elementFilter(int $iblockId, array $variables = []) : array
	{
		$filter = ['IBLOCK_ID' => $iblockId, '=ACTIVE' => 'Y'];

		if (isset($variables['ELEMENT_CODE']))
		{
			$filter['=CODE'] = $variables['ELEMENT_CODE'];
		}

		if (isset($variables['ELEMENT_ID']))
		{
			$filter['=ID'] = $variables['ELEMENT_ID'];
		}

		if (isset($variables['SECTION_CODE']))
		{
			$filter['=SECTION_CODE'] = $variables['SECTION_CODE'];
		}

		if (isset($variables['SECTION_ID']))
		{
			$filter['=SECTION_ID'] = $variables['SECTION_ID'];
		}

		return $filter;
	}

	protected static function getElementId(array $filter = []) : ?int
	{
		if (empty($filter)) { return null; }

		$query = \CIBlockElement::GetList([], $filter, false, false, ['ID']);

		$row = $query->Fetch();

		if (!$row) { return null; }

		return $row['ID'];
	}

	protected static function isSku(int $elementId) : bool
	{
		$environment = static::getEnvironment();
		$productEnvironment = $environment->getProduct();

		return $productEnvironment->isSku($elementId);
	}

	protected static function offerFilter(int $catalogIblockId) : array
	{
		$catalogIblockData = \CCatalogSKU::GetInfoByProductIBlock($catalogIblockId);
		$offerIblockId = $catalogIblockData ? $catalogIblockData['IBLOCK_ID'] : null;
		$code = null;
		$template = null;

		if ($offerIblockId === null) { return []; }

		$detailPageUrl = \CIBlock::GetArrayByID($offerIblockId, 'DETAIL_PAGE_URL');

		$symbolPos = mb_strpos($detailPageUrl, '?');

		if ($symbolPos !== false)
		{
			$queryParamOffer = mb_substr($detailPageUrl, $symbolPos + 1);
			[$code, $template] = explode('=', $queryParamOffer);
		}

		$request = static::getRequest(); // todo use iblock url template
		$filter = [];

		if ($code !== null && $template !== null && $request->get($code) !== null)
		{
			foreach (['ID', 'CODE'] as $key)
			{
				if (mb_strpos($template, $key) !== false)
				{
					$filter[$key] = $request->get($code);
					break;
				}
			}
		}

		if (empty($filter))
		{
			$filter = [
				'ID' => $request->get('OFFER_ID'),
				'CODE' => $request->get('OFFER_CODE'),
			];
		}

		return array_filter($filter);
	}

	protected static function searchOffer(int $iblockId, int $elementId, array $filter = []) : ?int
	{
		if (empty($filter)) { return null; }

		$environment = static::getEnvironment();
		$productEnvironment = $environment->getProduct();

		$offers = $productEnvironment->searchOffers($elementId, $iblockId, $filter);

		return !empty($offers) ? (int)reset($offers) : null;
	}

	protected static function getRequest()
	{
		return Main\Context::getCurrent()->getRequest();
	}

	protected static function getEnvironment() : TradingEntity\Reference\Environment
	{
		if (static::$environment === null)
		{
			static::$environment = TradingEntity\Registry::getEnvironment();
		}

		return static::$environment;
	}
}