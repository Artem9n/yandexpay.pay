<?php

$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_TYPE_SCHEDULE'] = 'Тип расписания доставки';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_TYPE_SCHEDULE_HELP'] = '<strong>Простой: </strong>
указывается ближайшая дата доставки, самая поздняя дата доставки, начало интервала времени доставки и конец интервала времени доставки
<br><br>
<strong>Гибкий: </strong> дата доставки выбирается покупателем в отрезке начала даты доставки и окончания даты доставки,
выбор интервала в течении дня на основание заполнения интервалов времени.';

$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_PLAIN'] = 'Простой';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_FLEXIBLE'] = 'Гибкий';

$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_DATE_INTERVAL'] = 'Интервал даты доставки';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_DATE_INTERVAL_HELP'] = 'По умолчанию, даты доставки берутся из "обработчка службы доставки", либо из "настроек обработчика/срок доставки(дней)"
<br><br>
Для <strong>простого</strong> типа расписания:
укажите конец интервала (второй параметр), для расчета даты доставки от текущего дня. <strong>Пример</strong>: текущая дата 01.01, указано значение 3 = 04.01.
<br><br>
Для <strong>гибкого</strong> типа расписания:
укажите начало интервала выбора даты доставки и конец интервала выбора даты доставки. <strong>Пример</strong>: текущая дата 01.01, начало интервала - 1, конец интервала - 3
= даты для доставок: 02.03, 03.03, 04.03.';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_TIME_INTERVAL'] = 'Интервал времени доставки';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_TIME_INTERVAL_HELP'] = 'Укажите один интервал времени для доставки';

$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_TYPE_TIME_INTERVALS'] = 'Тип временного интервала';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_GRID'] = 'Сетка';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_VALUES'] = 'Настраиваемый';

$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_NO_VALUE'] = '---Не выбрано---';

$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_DURATION_GRID'] = 'Продолжительность каждого интервала';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_FIELD_DURATION_GRID_REQUIRED'] = 'Не заполнено поле "Продолжительность каждого интервала"';

$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_START_TIME_GRID'] = 'Время начала самого первого интервала';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_FIELD_START_TIME_GRID_REQUIRED'] = 'Не заполнено поле "Время начала самого первого интервала"';

$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_END_TIME_GRID'] = 'Максимальное время начала самого последнего интервала';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_FIELD_END_TIME_GRID_REQUIRED'] = 'Не заполнено поле "Максимальное время начала самого последнего интервала"';

$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_STEP_TIME_GRID'] = 'Разница во времени между началами двух соседних интервалов';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_FIELD_STEP_TIME_GRID_REQUIRED'] = 'Не заполнено поле "Разница во времени между началами двух соседних интервалов"';

$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_TIME_INTERVAL_VALUES'] = 'Список интервалов времени';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_FIELD_TIME_INTERVALS_REQUIRED'] = 'Заполните хотя бы один интервал времени в "Список интервалов времени"';
$MESS['YANDEX_PAY_TRADING_SETTINGS_OPTIONS_COURIER_OPTIONS_TIME_INTERVAL_VALUES_HELP'] = 'Задаёт список интервалов напрямую. Подходит для небольшого количества интервалов доставки. Рекомендуемое максимальная количество интервалов - 20';