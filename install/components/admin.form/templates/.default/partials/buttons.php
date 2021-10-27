<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

use YandexPay\Pay;
use Bitrix\Main\Localization\Loc;

/**
 * @var array $arParams
 * @var array $arResult
 * @var string $templateFolder
 */

foreach ($arResult['BUTTONS'] as $button)
{
	$behavior = $button['BEHAVIOR'] ?? null;
	$buttonName = $button['NAME'] ?? null;
	$buttonAttributes = $button['ATTRIBUTES'] ?? [];

	switch ($behavior)
	{
		case 'previous':
			if ($arResult['STEP'] === 0)
			{
				$buttonName = Loc::getMessage('YANDEXPAY_PAY_T_ADMIN_FORM_BTN_CANCEL');
				$buttonAttributes += [
					'name' => 'cancel',
					'value' => 'Y',
				];
			}
			else
			{
				$buttonName = Loc::getMessage('YANDEXPAY_PAY_T_ADMIN_FORM_BTN_PREV_STEP');
				$buttonAttributes += [
					'name' => 'stepAction',
					'value' => 'previous',
				];
			}
		break;

		case 'next':
			if ($arResult['STEP_FINAL'])
			{
				$buttonName = $arParams['BTN_SAVE'] ?: Loc::getMessage('YANDEXPAY_PAY_T_ADMIN_FORM_BTN_SAVE');
				$buttonAttributes += [
					'class' => 'adm-btn adm-btn-save ' . ($arParams['ALLOW_SAVE'] ? '' : 'adm-btn-disabled'),
					'name' => 'save',
					'value' => 'Y',
					'disabled' => !$arParams['ALLOW_SAVE'],
				];
			}
			else
			{
				$buttonName = Loc::getMessage('YANDEXPAY_PAY_T_ADMIN_FORM_BTN_NEXT_STEP');
				$buttonAttributes += [
					'class' => 'adm-btn adm-btn-save',
					'name' => 'stepAction',
					'value' => 'next',
				];
			}
		break;

		case 'save':
			$buttonName = $arParams['BTN_SAVE'] ?: Loc::getMessage('YANDEXPAY_PAY_T_ADMIN_FORM_BTN_SAVE');
			$buttonAttributes += [
				'class' => 'adm-btn adm-btn-save ' . ($arParams['ALLOW_SAVE'] ? '' : 'adm-btn-disabled'),
				'name' => 'save',
				'value' => 'Y',
				'disabled' => !$arParams['ALLOW_SAVE'],
			];
		break;

		case 'apply':
			$buttonName = $arParams['BTN_APPLY'] ?: Loc::getMessage('YANDEXPAY_PAY_T_ADMIN_FORM_BTN_APPLY');
			$buttonAttributes += [
				'class' => 'adm-btn ' . ($arParams['ALLOW_SAVE'] ? '' : 'adm-btn-disabled'),
				'name' => 'apply',
				'value' => 'Y',
				'disabled' => !$arParams['ALLOW_SAVE'],
			];
		break;

		case 'reset':
			$buttonName = Loc::getMessage('YANDEXPAY_PAY_T_ADMIN_FORM_BTN_RESET');
			$buttonAttributes += [
				'class' => 'adm-btn',
				'type' => 'reset',
			];
		break;
	}

	$buttonAttributes += [
		'class' => 'adm-btn',
		'type' => 'submit',
	];
	$buttonAttributesString = Pay\Ui\UserField\Helper\Attributes::stringify($buttonAttributes);

	?>
	<button <?= $buttonAttributesString; ?>><?= $buttonName; ?></button>
	<?php
}