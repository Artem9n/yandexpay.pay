<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

/**
 * @var array $params
 * @var \Sale\Handlers\PaySystem\YandexPayHandler $this
 * @var CMain $APPLICATION
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main\UI\Extension;

Loc::loadMessages(__FILE__);

// assets

if ($APPLICATION->GetPageProperty('yandexpay_extension_widget') !== 'Y')
{
	$APPLICATION->SetPageProperty('yandexpay_extension_widget', 'Y');
	echo Extension::getHtml('yandexpaypay.widget');
}

$widgetOptions = array_intersect_key($params, [
	'requestSign' => true,
	'env' => true,
	'merchantId' => true,
	'merchantName' => true,
	'buttonTheme' => true,
	'buttonWidth' => true,
	'cardNetworks' => true,
	'gateway' => true,
	'gatewayMerchantId' => true,
	'externalId' => true,
    'paySystemId' => true,
    'currency' => true,
	'notifyUrl' => true,
	'restUrl' => true,
	'successUrl' => true,
	'failUrl' => true,
	'isRest' => true,
	'metadata' => true,
]);

// widget index

$widgetIndex = (int)$APPLICATION->GetPageProperty('yandexpay_widget_index');
$containerId = 'yandexpay' . ($widgetIndex > 0 ? '-' . $widgetIndex : '');

$APPLICATION->SetPageProperty('yandexpay_widget_index', ++$widgetIndex);

//draw

$factoryOptions = array_intersect_key($params, [
	'buttonWidth' => true,
]);

$position = 'afterbegin';
$selector = '#' . $containerId . '-container';
$factoryOptions += [
	'preserve' => false,
	'containerId' => $containerId,
];
?>

<div>
	<?= Loc::getMessage('YANDEX_MARKET_SALE_SUM_DESCRIPTION', ['#SUM#' => CurrencyFormat($params['order']['total'], $params['currency'])])?>
</div>
<div id="<?= $containerId . '-container' ?>"></div>

<script>
	(function() {
		<?php
		echo file_get_contents(__DIR__ . '/init.min.js');
		?>
		function run() {
			const factory = new BX.YandexPay.Factory(<?= Json::encode($factoryOptions) ?>);
			const selector = '<?= htmlspecialcharsback($selector) ?>';
			const position = '<?= $position?>';

			factory.inject(selector, position)
				.then((widget) => {
					widget.setOptions(<?= Json::encode($widgetOptions) ?>);
					widget.payment(<?= Json::encode($params['order']) ?>);
				})
				.catch((error) => {
					console.warn(error);
				});
		}
	})();
</script>
