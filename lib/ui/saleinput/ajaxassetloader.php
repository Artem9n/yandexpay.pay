<?php
namespace YandexPay\Pay\Ui\SaleInput;

use Bitrix\Main;

class AjaxAssetLoader
{
	public static function isLoad() : bool
	{
		$request = Main\Application::getInstance()->getContext()->getRequest();

		return mb_strpos($request->getRequestUri(), 'sale_pay_system_ajax.php') !== false
			&& $request->get('action') === 'getHandlerDescription'
			&& $request->get('handler') === 'yandexpay'
			&& !isset($GLOBALS['YAPAY_REGISTERED']);
	}

	public static function loadAssets() : void
	{
		global $APPLICATION;

		$GLOBALS['YAPAY_REGISTERED'] = true;

		\CJSCore::Init(); // preload core, for skip on ajax

		$assets = Main\Page\Asset::getInstance();
		$assets = $assets->setAjax();
		$APPLICATION->oAsset = $assets;

		AddEventHandler('main', 'OnEndBufferContent', function(&$content) {
			try
			{
				$data = Main\Web\Json::decode($content);

				if (!isset($data['BUS_VAL'])) { return; }

				$assets = Main\Page\Asset::getInstance();
				$externalJs = [];

				$jsContent = $assets->getJs();
				$jsContent = preg_replace_callback('/<script.*? src="(?<path>.*?)".*?><\/script>/', static function(array $matches) use (&$externalJs) {
					$externalJs[] = $matches['path'];
					return '';
				}, $jsContent);

				if (!empty($externalJs))
				{
					$jsContent .= sprintf('<script type="text/javascript">
						BX.loadScript(%s, function() { 
				            BX.onCustomEvent(document, "onYaPayContentUpdate", [
				              { target: document }
				            ]); 
			          });
		            </script>', Main\Web\Json::encode($externalJs));
				}

				$data['BUS_VAL'] = $assets->getCss() . $jsContent . $data['BUS_VAL'];

				$content = Main\Web\Json::encode($data);
			}
			catch (Main\SystemException $exception)
			{
				trigger_error($exception->getMessage(), E_USER_WARNING);
			}
		});
	}
}