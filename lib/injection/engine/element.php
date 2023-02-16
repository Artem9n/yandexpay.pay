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
	protected static $disabled = false;

	public static function onEpilog(int $injectionId, array $settings) : void
	{
		if (self::$disabled || !static::testShow($settings)) { return; }

		[ $elementId, $products ] = static::findProduct($settings);

		if ($elementId === null) { return; }

		[ $componentParameters ] = static::getRenderParameters($injectionId, [
			'SITE_ID' => $settings['SITE_ID'],
			'PRODUCT_ID' => $elementId,
			'PRODUCTS' => $products,
		]);

		if (!static::testRender($componentParameters)) { return; }

		static::render($componentParameters);
	}

	public static function disable() : void
	{
		self::$disabled = true;
	}

	protected static function findProduct(array $settings) : array
	{
		try
		{
			$template = static::iblockTemplate($settings['IBLOCK']);
			$variables = static::parseTemplate($template);
			$elementFilter = static::elementFilter($settings['IBLOCK'], $variables);

			if (empty($elementFilter))
			{
				throw new Main\SystemException('cant build element filter');
			}

			$elementId = static::searchElement($elementFilter);
			$products = static::findProducts($settings['IBLOCK'], $elementId);

			if (!isset($products[$elementId])) // isSku
			{
				$offerId = static::resolveOffer($settings['IBLOCK'], $products, [
					'PRODUCT_URL' => \CComponentEngine::makePathFromTemplate($template, $variables),
				]);

				$selectedId = $offerId ?? $elementId;
			}
			else
			{
				$selectedId = $elementId;
			}
		}
		catch (Main\ArgumentException $exception)
		{
			$selectedId = null;
			$products = null;
		}
		catch (Main\SystemException $exception)
		{
			$logger = new Logger\Logger();
			$logger->warning(...(new Logger\Formatter\Exception($exception, [
				'AUDIT' => Logger\Audit::INJECTION_ELEMENT,
				'URL' => static::getRequest()->getRequestUri(),
			]))->forLogger());
			$selectedId = null;
			$products = null;
		}

		return [$selectedId, $products];
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
			'EXTERNAL_ID' => true,
			'XML_ID' => true,
		];

		if (count(array_intersect_key($variables, $required)) === 0)
		{
			return [];
		}

		$map = [
			'ELEMENT_CODE' => '=CODE',
			'CODE' => '=CODE',
			'ELEMENT_ID' => '=ID',
			'ID' => '=ID',
			'SECTION_CODE' => '=SECTION_CODE',
			'SECTION_ID' => '=SECTION_ID',
			'EXTERNAL_ID' => '=XML_ID',
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

	protected static function findProducts(int $productIblockId, int $productId) : array
	{
		$environmentProduct = static::getEnvironment()->getProduct();
		$productData = $environmentProduct->productData($productId);

		if ($environmentProduct->isSku($productData))
		{
			$result = $environmentProduct->searchOffers($productId, $productIblockId);
		}
		else if ($environmentProduct->isOffer($productData))
		{
			$productId = $environmentProduct->searchProductId($productId, static::offerIblock($productIblockId));
			$result = $environmentProduct->searchOffers($productId, $productIblockId);
		}
		else
		{
			$result = [
				$productId => $productData,
			];
		}

		return $result;
	}

	protected static function resolveOffer(int $productIblockId, array $products, array $defined = []) : ?int
	{
		try
		{
			$offerIblock = static::offerIblock($productIblockId);
			$offerTemplate = static::iblockTemplate($offerIblock, static::defaultOfferTemplate());
			$offerVariables = static::parseTemplate($offerTemplate, $defined);
			$offerFilter = static::elementFilter($offerIblock, $offerVariables);

			$result = static::selectOffer($offerIblock, $products, $offerFilter);
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
		$request = static::getRequest();

		$matched = $engine->guessComponentPath(
			$sefFolder,
			[ 'target' => $templatePage ],
			$variables,
			$request->getRequestedPage()
		);

		if (!$matched && $greedyPart !== '')
		{
			$templatePage .= $greedyPart;

			$matched = $engine->guessComponentPath(
				$sefFolder,
				[ 'target' => $templatePage ],
				$variables,
				urldecode($request->getRequestedPage())
			);
		}

		if ($matched !== 'target')
		{
			return [];
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

		return str_replace('//', '/', $template);
	}

	protected static function selectOffer(int $iblockId, array $products, array $filter = []) : ?int
	{
		$productEnvironment = static::getEnvironment()->getProduct();

		return $productEnvironment->selectOffer($iblockId, $products, $filter);
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