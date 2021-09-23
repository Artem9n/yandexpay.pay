<?php

$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_DESCRIPTION'] = '<a target="_blank" href="https://rbk.money/">RBK.money</a> – международная платежная платформа, </br>предоставляющая услуги для онлайн-компаний и частных лиц в более чем 60 странах.';

$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_MERCHANT_ID'] = 'Идентификатор продавца';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_MERCHANT_API_KEY'] = 'API-ключ для авторизации';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_MERCHANT_SHOP_ID'] = 'Ключ магазина';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_WEBHOOK_PROCESSED_KEY'] = 'Публичный ключ Webhook PaymentProcessed';

$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_PRODUCT_NAME'] = 'Номер заказа № #ORDER_ID#';

$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_REFUNDED_STATUS'] = 'По данному заказу был совершен возврат';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_FAILED_STATUS'] = 'Ошибка оплаты';

$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_ambiguousPartyID'] = 'Невозможно однозначно определить идентификатор участника, укажите идентификатор в запросе явно.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_changesetConflict'] = 'Попытка внести изменения участника, конфликтующие с изменениями в других заявках, ожидающих рассмотрения.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_chargebackInProgress'] = 'Попытка возврата при открытом возвратном платеже.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_inconsistentRefundCurrency'] = 'Попытка возврата средств в валюте, отличной от валюты платежа.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_insufficentAccountBalance'] = 'Недостаточный объём денежных средств на счёте магазина, например, для проведения возврата.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invalidChangeset'] = 'Неверные изменения участника, например, попытка создать магазин в валюте, недоступной в рамках договора.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invalidClaimRevision'] = 'Неверная ревизия заявки. Например, в случае если заявку одновременно с вами кто-то уже принял или отклонил.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invalidClaimStatus'] = 'Неверный статус заявки. Например, при попытке отзыва уже принятой заявки.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invalidContractStatus'] = 'Ваш договор более не имеет силы, по причине истечения срока действия или расторжения.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invalidDeadline'] = 'Неверный формат времени.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invalidInvoiceCart'] = 'Некорректная корзина в инвойсе, Например, пустая.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invalidInvoiceCost'] = 'Стоимость инвойса не указана или неверна, в частности, не равна стоимости позиций в корзине.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invalidInvoiceStatus'] = 'Неверный статус инвойса. Например, при попытке оплатить отменённый инвойс.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invalidPartyID'] = 'Участник с указанным идентификатором не существует или недоступен.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invalidPartyStatus'] = 'Ваш участник заблокирован или его операции приостановлены. В последнем случае вы можете их возобновить.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invalidPaymentResource'] = 'Не поддерживаемый системой или не подключенный в рамках действующего договора платежный инструмент.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invalidPaymentSession'] = 'Невернoе содержимое платёжной сессии.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invalidPaymentStatus'] = 'Неверный статус платежа. Например, при попытке подтвердить неуспешный платёж.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invalidPaymentToolToken'] = 'Неверное содержимое токена платёжного инструмента.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invalidProcessingDeadline'] = 'Неверный формат ограничения времени авторизации платежа.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invalidRecurrentParent'] = 'Невернo указан родительский рекуррентный платеж.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invalidRequest'] = 'Прочие неверные данные запроса.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invalidShopID'] = 'Магазин с указанным идентификатором не существует или недоступен.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invalidShopStatus'] = 'Ваш магазин заблокирован или его операции приостановлены. В последнем случае вы можете их возобновить.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invoicePaymentAmountExceeded'] = 'Попытка возврата сверх суммы платежа.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invoicePaymentPending'] = 'Последний запущенный платёж по указанному инвойсу ещё не достиг финального статуса.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_invoiceTermsViolated'] = 'Инвойс нарушает ограничения, установленные в рамках действующего договора.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_limitExceeded'] = 'Превышен разумный лимит выборки. В этом случае лучше запросить менее объёмный набор данных.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_operationNotPermitted'] = 'Недоступная в рамках действующего договора операция.';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_timeout'] = 'Истекло время ожидания попытки оплаты';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_rejected_by_inspector'] = 'Отклонено сервисом противодействия мошенничеству';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_preauthorization_failed'] = 'Ошибка предавторизации (3DS)';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_InvalidPaymentTool'] = 'Неверный платежный инструмент (введен номер несуществующей карты, отсутствующего аккаунта и т.п.)';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_AccountLimitsExceeded'] = 'Превышены лимиты (например, в личном кабинете плательщика установлено ограничение по сумме платежа, стране списания)';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_InsufficientFunds'] = 'Недостаточно средств на счете';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_PreauthorizationFailed'] = 'Предварительная авторизация отклонена (введен неверный код 3D-Secure, на форме 3D-Secure нажата ссылка отмены)';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_RejectedByIssuer'] = 'Платёж отклонён эмитентом (установлены запреты по стране списания, запрет на покупки в интернете, платеж отклонен антифродом эмитента и т.п.)';
$MESS['YANDEX_PAY_GATEWAY_PAYMENT_RBKMONEY_PaymentRejected'] = 'Платёж отклонён по иным причинам';
