<?php
namespace YandexPay\Pay\Ui\SaleInput;

use Bitrix\Main;
use Bitrix\Sale\Internals;
use YandexPay\Pay;
use YandexPay\Pay\Ui;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity as TradingEntity;

if (!Main\Loader::includeModule('sale')) { return; }

class MerchantType extends Internals\Input\StringInput
{
	use Concerns\HasMessage;

	protected static $environment;

	public static function getEditHtmlSingle($name, array $input, $value) : string
	{
		//$result = parent::getEditHtmlSingle($name, $input, $value);
		$result = static::getButtonHtml();

		return $result;
	}

	protected static function getButtonHtml() : string
	{
		Main\UI\Extension::load('yandexpaypay.admin.ui.merchantbutton');
		Main\UI\Extension::load('yandexpaypay.admin.ui.merchantrest');
		Main\UI\Extension::load('yandexpaypay.admin.ui.merchantconsole');

		$plugin = 'ButtonField';
		$attributes = [];

		if (!Ui\Admin\PaySystemEditPage::validateMerchantButton())
		{
			$plugin = 'ConsoleField';
			$attributes = [
				'data-merchant-url' => static::getDomain(),
				'data-callback-url' => static::getCallbackUrl(),
				'data-merchant-token' => static::getMerchantToken(),
			];
		}

		$attributes += [
			'type' => 'button',
			'value' => self::getMessage('INPUT_NAME'),
			'class' => 'adm-btn-green js-plugin-click',
			'data-plugin' => sprintf('Ui.%s', $plugin),
			'data-form-url' => Ui\Admin\Path::getModuleUrl('trading_merchant', ['lang' => LANGUAGE_ID, 'view' => 'dialog']),
			'data-form-title' => self::getMessage('MODAL_TITLE'),
			'data-form-save-title' => self::getMessage('BTN_SAVE'),
			'data-not-creds' => self::getMessage('NOT_CREDS'),
		];

		return sprintf('<input %s>', Ui\UserField\Helper\Attributes::stringify($attributes));
	}

	protected static function getDomain() : string
	{
		$environment = static::getEnvironment();

		$params = [
			'host' => Pay\Data\SiteDomain::getHost($environment->getSite()->getDefault()),
		];

		return Pay\Utils\Url::absolutizePath('', $params);
	}

	protected static function getEnvironment() : TradingEntity\Reference\Environment
	{
		if (static::$environment === null)
		{
			static::$environment = TradingEntity\Registry::getEnvironment();
		}

		return static::$environment;
	}

	protected static function getCallbackUrl() : string
	{
		$environment = static::getEnvironment();

		$siteId = $environment->getSite()->getDefault();

		$params = [
			'host' => Pay\Data\SiteDomain::getHost($siteId),
		];

		$routPath = $environment->getRoute()->getPublicPath();

		$domain = rtrim(Pay\Utils\Url::absolutizePath('', $params), '/');

		return $domain . $routPath;
	}

	protected static function getMerchantToken() : string
	{
		$token = Pay\Config::getOption('merchant_token', null);

		if ($token === null)
		{
			$token = md5(microtime() . 'salt' . time());
			Pay\Config::setOption('merchant_token', $token);
		}

		return $token;
	}
}