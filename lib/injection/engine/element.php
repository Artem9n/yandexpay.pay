<?php
namespace YandexPay\Pay\Injection\Engine;

use Bitrix\Main;
use YandexPay\Pay\Config;
use YandexPay\Pay\Logger;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Entity as TradingEntity;

class Element extends AbstractEngine
{
	protected static $environment;

	public static function onEpilog(int $injectionId, array $settings) : void
	{
		if (!static::testRequest()) { return; }

		$elementId = static::findProduct($settings);

		if ($elementId === null) { return; }

		static::render($injectionId, ['PRODUCT_ID' => $elementId, 'SITE_ID' => $settings['SITE_ID']]);
	}

	protected static function findProduct(array $settings) : ?int
	{
		try
		{
			$template = static::iblockTemplate($settings['IBLOCK']);
			$variables = static::parseTemplate($template);
			$elementFilter = static::elementFilter($settings['IBLOCK'], $variables);
			$elementId = static::searchElement($elementFilter);
			$offerId = static::resolveOffer($settings['IBLOCK'], $elementId, [
				'PRODUCT_URL' => \CComponentEngine::makePathFromTemplate($template, $variables),
			]);

			$result = $offerId ?? $elementId;
		}
		catch (Main\ArgumentException $exception)
		{
			$result = null;
		}
		catch (Main\SystemException $exception)
		{
			$logger = new Logger\Logger();
			$logger->warning(...(new Logger\Formatter\Exception($exception, [
				'AUDIT' => Logger\Audit::INJECTION_ELEMENT,
				'URL' => static::getRequest()->getRequestUri(),
			]))->forLogger());
			$result = null;
		}

		return $result;
	}

	protected static function iblockTemplate(int $iblockId = null, string $default = null) : string
	{
		static::loadModule('iblock');

		$template = \CIBlock::GetArrayByID($iblockId, 'DETAIL_PAGE_URL');
		$template = trim($template);

		if ($template === '')
		{
			$template = $default ?? null;
		}

		Assert::notNull($template, 'template');

		return $template;
	}

	protected static function defaultOfferTemplate() : string
	{
		return '#PRODUCT_URL#?OFFER_ID=#ELEMENT_ID#&OFFER_CODE=#ELEMENT_CODE#';
	}

	protected static function elementFilter(int $iblockId, array $variables = []) : array
	{
		$required = [
			'ELEMENT_ID' => true,
			'ELEMENT_CODE' => true,
			'ID' => true,
			'CODE' => true,
		];

		if (count(array_intersect_key($variables, $required)) === 0)
		{
			throw new Main\SystemException('cant build element filter');
		}

		$map = [
			'ELEMENT_CODE' => '=CODE',
			'CODE' => '=CODE',
			'ELEMENT_ID' => '=ID',
			'ID' => '=ID',
			'SECTION_CODE' => '=SECTION_CODE',
			'SECTION_ID' => '=SECTION_ID',
		];
		$filter = [
			'IBLOCK_ID' => $iblockId,
			'=ACTIVE' => 'Y',
			'INCLUDE_SUBSECTIONS' => 'Y',
		];

		foreach ($map as $from => $to)
		{
			if (!array_key_exists($from, $variables)) { continue; }

			$filter[$to] = $variables[$from];
		}

		return $filter;
	}

	protected static function searchElement(array $filter = []) : int
	{
		$query = \CIBlockElement::GetList([], $filter, false, ['nTopCount' => 1], ['ID']);
		$row = $query->Fetch();

		if (!$row)
		{
			throw new Main\ArgumentException('cant find element');
		}

		return (int)$row['ID'];
	}

	protected static function resolveOffer(int $productIblockId, int $productId, array $defined = []) : ?int
	{
		try
		{
			$offerIblock = static::offerIblock($productIblockId);
			$offerTemplate = static::iblockTemplate($offerIblock, static::defaultOfferTemplate());
			$offerVariables = static::parseTemplate($offerTemplate, $defined);
			$offerFilter = static::elementFilter($offerIblock, $offerVariables);

			if (!static::isSku($productId)) { return null; }

			$result = static::searchOffer($productIblockId, $productId, $offerFilter);
		}
		catch (Main\SystemException $exception)
		{
			$result = null;
		}

		return $result;
	}

	protected static function offerIblock(int $catalogIblockId) : int
	{
		static::loadModule('catalog');

		$catalogIblockData = \CCatalogSKU::GetInfoByProductIBlock($catalogIblockId);

		if (!isset($catalogIblockData['IBLOCK_ID']))
		{
			throw new Main\ArgumentException('has not offer iblock');
		}

		return (int)$catalogIblockData['IBLOCK_ID'];
	}

	protected static function parseTemplate(string $template, array $defined = []) : ?array
	{
		$template = static::compileUrlTemplate($template, $defined);
		[$templatePage, $templateQuery] = explode('?', $template, 2);

		$pageVariables = static::parsePageTemplate((string)$templatePage);
		$queryVariables = static::parseQueryTemplate((string)$templateQuery);

		return $pageVariables + $queryVariables;
	}

	protected static function parsePageTemplate(string $templatePage) : array
	{
		$engine = new \CComponentEngine();

		if (Main\Loader::includeModule('iblock'))
		{
			$engine->addGreedyPart('#SECTION_CODE_PATH#');
			$engine->setResolveCallback(['CIBlockFindTools', 'resolveComponentEngine']);
		}

		$sefFolder = '/';
		$templatePage = mb_substr($templatePage, mb_strlen($sefFolder));
		$greedyPart = (string)Config::getOption('injection_engine_element_greedy', '');
		$url = static::getRequest()->getRequestedPage();

		$matched = $engine->guessComponentPath(
			$sefFolder,
			[ 'target' => $templatePage ],
			$variables,
			$url
		);

		if (!$matched && $greedyPart !== '')
		{
			$templatePage .= $greedyPart;

			$matched = $engine->guessComponentPath(
				$sefFolder,
				[ 'target' => $templatePage ],
				$variables,
				urldecode($url)
			);
		}

		if ($matched !== 'target')
		{
			throw new Main\ArgumentException('page not matched');
		}

		return $variables;
	}

	protected static function parseQueryTemplate(string $templateQuery) : array
	{
		$request = static::getRequest();
		$result = [];

		foreach (explode('&', $templateQuery) as $queryTemplate)
		{
			[$name, $valueTemplate] = explode('=', $queryTemplate, 2);

			if (!preg_match('/^#([A-Z_]+)#$/', $valueTemplate, $valueMatches)) { continue; }

			$value = $request->get($name);

			if (!is_scalar($value) || trim($value) === '') { continue; }

			$result[$valueMatches[1]] = $value;
		}

		return $result;
	}

	protected static function compileUrlTemplate(string $template, array $defined = []) : string
	{
		$template = \CComponentEngine::makePathFromTemplate($template);

		foreach ($defined as $key => $value)
		{
			$template = str_replace('#' . $key . '#', $value, $template);
		}

		$template = str_replace('//', '/', $template);

		return $template;
	}

	protected static function isSku(int $elementId) : bool
	{
		$environment = static::getEnvironment();
		$productEnvironment = $environment->getProduct();

		return $productEnvironment->isSku($elementId);
	}

	protected static function searchOffer(int $iblockId, int $elementId, array $filter = []) : ?int
	{
		if (empty($filter)) { return null; }

		$environment = static::getEnvironment();
		$productEnvironment = $environment->getProduct();

		$offers = $productEnvironment->searchOffers($elementId, $iblockId, $filter);

		return !empty($offers) ? (int)reset($offers) : null;
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