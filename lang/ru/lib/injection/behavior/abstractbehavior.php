<?php

$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_POSITION'] = 'Позиция добавляемой кнопки относительно селектора';
$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_SELECTOR'] = 'Селектор, куда привязывать кнопку "Yandex Pay"';
$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_HELP_SELECTOR'] = 'Укажите css селектор (селекторы через <strong>", "</strong>)(запятую), куда будет привязана кнопка Yandex Pay.
<br><br>Есть возможность настроить привязку для <strong>десктопа</strong> и <strong>мобильной</strong> версиях отображения, разные опции.<br><br>
Создайте два шаблона, например "Карточка товара", укажите селекторы для разных версий отображения.<br><br>
Пример "Карточки товара" мобильной версии: .ваш селектор<strong>:media(max-width: 700px)</strong><br>
Оформление/Отображение/Кнопка
<br><br>
Пример "Карточки товара" десктопной версии: .ваш селектор<strong>:media(min-width: 700px)</strong><br>
Оформление/Отображение/Виджет';

$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_GROUP_POSITION'] = 'Позиционирование';
$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_GROUP_DECOR'] = 'Оформление';

$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_POSITION_BEFOREBEGIN'] = 'перед';
$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_POSITION_AFTERBEGIN'] = 'внутри, в начале';
$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_POSITION_BEFOREEND'] = 'внутри, в конце';
$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_POSITION_AFTEREND'] = 'после';

$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_DISPLAY'] = 'Отображение';
$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_HELP_DISPLAY'] = 'Внешний вид виджета/кнопки Yandex Pay может меняться в зависимости от текущего браузера, результата скоринга, 
<br>авторизации на ya.ru, наличия аватара или привязанной банковской карты.';
$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_USE_DIVIDER'] = 'Использовать разделитель';

$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_EXPERT_FIELDS'] = 'Экспертные настройки';
$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_CSS'] = 'Css';
$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_JS'] = 'Js';
$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_CSS_CONTENT'] = 'Css контент';
$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_JS_CONTENT'] = 'Js контент';
$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_EXPERT_CSS_HELP'] = 'Возможность добавить собственные css стили';
$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_EXPERT_JS_HELP'] = 'Возможность добавить собственный js код';
$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_EXPERT_CSS_CONTENT_HELP'] = 'Добавьте собственные css стили. 
<br>
<br>
Для установки динамического id контейнера, укажите <strong>{$container}</strong>, пример: {$container} .ya-pay-button';
$MESS['YANDEX_PAY_INJECTION_BEHAVIOR_ABSTRACT_BEHAVIOR_EXPERT_JS_CONTENT_HELP'] = 'Добавьте собственный js код';