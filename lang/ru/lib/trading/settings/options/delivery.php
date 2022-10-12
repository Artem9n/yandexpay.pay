<?php

$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_ID'] = 'Служба доставки';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_TYPE'] = 'Способ доставки';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_TYPE_PICKUP'] = 'Самовывоз';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_TYPE_DELIVERY'] = 'Курьерская доставка';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_TYPE_YANDEX_DELIVERY'] = 'Яндекс.Доставка';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_GROUP_SETTINGS'] = 'Настройки Яндекс.Доставка';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_CATALOG_STORE'] = 'Выбор склада';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_CATALOG_STORE_HELP'] = '<strong>Только один склад</strong> — при расчете доставки будет учтен только один склад;
<br><br><strong>Ближайший склад</strong> — при расчете доставки будут учтены только склады, в которых все товары корзины в наличии. В качестве склада отгрузки будет выбран ближайший к покупателю.';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_CATALOG_STORE_DEFAULT'] = 'Только один склад';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_WAREHOUSE'] = 'Адрес склада';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_WAREHOUSE_HELP'] = 'Укажите адрес склада для расчета доставки.';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_EMERGENCY_CONTACT'] = 'Контактное лицо';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_EMERGENCY_CONTACT_HELP'] = 'Выберите пользователя в качестве Контактного лица. 
<br><br>Обязательные поля пользователя <strong>Имя</strong>, <strong>Фамилия</strong>, <strong>Отчество</strong>, <strong>Email</strong>, <strong>Телефон</strong> или <strong>Мобильный</strong>.';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_STORE_WAREHOUSE'] = 'Адрес склада';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_SHIPMENT_SCHEDULE'] = 'График склада';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_SHIPMENT_SCHEDULE_HELP'] = 'Настройте режим работы склада магазина.';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_STORE_WAREHOUSE_HELP'] = '<a target="_blank" href="#LINK#">Создайте</a> пользовательское свойство склада, выбрав тип "Яндекс.Pay: адрес склада"';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_STORE_CONTACT'] = 'Контактное лицо';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_STORE_CONTACT_HELP'] = '<a target="_blank" href="#LINK#">Создайте</a> пользовательское свойство склада, выбрав тип "Яндекс.Pay: контакт пользователя"';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_STORE_SHIPMENT_SCHEDULE'] = 'График склада';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_STORE_SHIPMENT_SCHEDULE_HELP'] = '<a target="_blank" href="#LINK#">Создайте</a> пользовательское свойство склада, выбрав тип "Яндекс.Pay: график склада"';

$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_FIELD_EMERGENCY_REQUIRED'] = 'не заполнено поле Контактное лицо';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_FIELD_STORE_CONTACT_REQUIRED'] = 'не установлено пользовательское свойство склада "Яндекс.Pay: адрес склада"';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_FIELD_STORE_WAREHOUSE_REQUIRED'] = 'не установлено пользовательское свойство склада "Яндекс.Pay: контакт пользователя"';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_FIELD_STORE_SHIPMENT_SCHEDULE_REQUIRED'] = 'не установлено пользовательское свойство склада "Яндекс.Pay: график склада"';

$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_COURIER_SCHEDULE'] = 'Расписание';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_COURIER_SCHEDULE_HELP'] = 'На основе расписания будет сформирован список возможных дат доставки и временных интервалов, доступных покупателю для выбора. <br><br>На каждый день можно создать до 5 интервалов.';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_COURIER_GROUP'] = 'График доставки';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_SHIPMENT_DELAY'] = 'Задержка отгрузки';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_SHIPMENT_DELAY_HELP'] = 'Время необходимое магазину для передачи заказа в службу доставки';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_PERIOD_WEEKEND_RULE'] = 'Учет выходных';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_PERIOD_WEEKEND_RULE_HELP'] = 'Как будет изменена первая дата доставки, если срок доставки от 2 до 4 дней:
<ul>
<li>Срок доставки указан в рабочих днях — второй рабочий день ^1;</li>
<li>Служба доставки учитывает выходные — второй день.</li>
</ul>
^1 если заказ выполняется в нерабочее время с учетом задержки отгрузки, отсчет начнется со следующего рабочего дня.';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_PERIOD_WEEKEND_RULE_FULL'] = 'Срок доставки указан в рабочих днях';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_DELIVERY_PERIOD_WEEKEND_RULE_NONE'] = 'Служба доставки учитывает выходные';