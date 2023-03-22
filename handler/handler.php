<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Request;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\PaySystem\ServiceResult;
use YandexPay\Pay\Config;
use YandexPay\Pay\Exceptions\Secure3dRedirect;
use YandexPay\Pay\Gateway;
use YandexPay\Pay\Logger;
use YandexPay\Pay\Psr;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Entity\Registry;
use YandexPay\Pay\Ui\Admin\PaySystemEditPage;
use YandexPay\Pay\Utils\Url;
use YandexPay\Pay\Trading\Action\Api;
use YandexPay\Pay\Trading\Setup as TradingSetup;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

Loader::includeModule('yandexpay.pay');

class YandexPayHandler extends PaySystem\ServiceHandler implements PaySystem\IRefund, PaySystem\IHold
{
	public const REQUEST_SIGN = 'yandexpay';

	protected const STEP_3DS = '3ds';
	protected const STEP_FINISHED = 'finished';
	protected const STEP_FAILURE = 'errors';

	protected const YANDEX_TEST_MODE = 'SANDBOX';
	protected const YANDEX_PRODUCTION_MODE = 'PRODUCTION';

	/** @var Gateway\Base|null */
	protected $gateway;

	/**
	 * @inheritDoc
	 */
	public function initiatePay(Payment $payment, Request $request = null) : PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		if ($this->isPaymentAuthorized($payment)) { return $result; }

		try
		{
			$this->resolvePaymentNumber($payment);
	        $this->setExtraParams($this->getParams($payment));

	        $showTemplateResult = $this->showTemplate($payment, 'template');

	        if ($showTemplateResult->isSuccess())
	        {
		        $result->setTemplate($showTemplateResult->getTemplate());
				$this->setRedirectUrl($payment);
	        }
	        else
            {
	            $result->addErrors($showTemplateResult->getErrors());
            }
        }
        catch (Main\SystemException $exception)
        {
	        $result->addError(new Main\Error($exception->getMessage()));
        }

        return $result;
	}

	protected function isPaymentAuthorized(Payment $payment) : bool
	{
		$status = $payment->getField('PS_STATUS_CODE');

		return $status === EntitySale\Status::PAYMENT_STATUS_AUTHORIZE;
	}

	protected function resolvePaymentNumber(Payment $payment) : void
	{
		if ((string)$payment->getField('PS_INVOICE_ID') !== '') { return; }

		$order = $payment->getOrder();
		$accountNumber = (string)$order->getField('ACCOUNT_NUMBER');

		foreach ($payment->getCollection() as $sibling)
		{
			if ((string)$sibling->getField('PS_INVOICE_ID') === $accountNumber)
			{
				$accountNumber = $payment->getField('ACCOUNT_NUMBER');
				break;
			}
		}

		$payment->setField('PS_INVOICE_ID', $accountNumber);
		$order->save();
	}

	protected function setRedirectUrl(Payment $payment) : void
	{
		global $APPLICATION;

		unset($_SESSION['yabackurl']);
		$_SESSION['yabackurl'] = Url::absolutizePath() . $APPLICATION->GetCurPageParam();
	}

	protected function getParams(Payment $payment) : array
	{
		global $APPLICATION;

		$gateway = $this->wakeUpGateway($payment);
		$isRest = $gateway->isRest();
		$environment = Registry::getEnvironment();
		$curPage = $APPLICATION->GetCurPage(false);
		$successUrl = $this->getParamValue($payment, 'SUCCESS_URL') ?: null;
		$failUrl = $this->getParamValue($payment, 'FAIL_URL') ?: null;

		return [
			'requestSign'           => static::REQUEST_SIGN,
			'order'                 => $isRest ? $this->getOrderRest($payment) : $this->getOrderData($payment),
			'env'                   => $this->isTestMode($payment) ? self::YANDEX_TEST_MODE : self::YANDEX_PRODUCTION_MODE,
			'merchantId'            => $this->getParamValue($payment, 'MERCHANT_ID'),
			'merchantName'          => $this->getParamValue($payment, 'MERCHANT_NAME'),
			'buttonTheme'           => $this->getParamValue($payment, 'VARIANT_BUTTON'),
			'buttonWidth'           => $this->getParamValue($payment, 'WIDTH_BUTTON'),
			'gateway'               => $gateway->getGatewayId(),
			'gatewayMerchantId'     => $gateway->getMerchantId(),
			'externalId'            => $payment->getId(),
			'paySystemId'           => $this->service->getField('ID'),
			'currency'              => $payment->getField('CURRENCY'),
			'notifyUrl'             => $this->getParamValue($payment, 'NOTIFY_URL'),
			'restUrl'               => $environment->getRoute()->getPublicPath(),
			'successUrl'            => $successUrl ?? $curPage,
			'failUrl'               => $failUrl ?? $curPage,
			'isRest'                => $isRest,
			'metadata'              => $this->makeMetadata($payment),
		];
	}

	protected function makeMetadata(Payment $payment) : string
	{
		$userId = $payment->getOrder()->getUserId();
		$setupId = $this->loadSetupId($payment);

		return implode(':', [$userId, $userId, $setupId]);
	}

	protected function loadSetupId(Payment $payment) : ?int
	{
		$result = null;

		$query = TradingSetup\RepositoryTable::getList([
			'filter' => [
				'=SITE_ID' => $payment->getOrder()->getSiteId(),
				'=PERSON_TYPE_ID' => $payment->getPersonTypeId(),
				'ACTIVE' => true,
			],
			'limit' => 1,
		]);

		if ($setup = $query->fetchObject())
		{
			$result = $setup->getId();
		}

		return $result;
	}

	protected function getOrderData(Payment $payment): array
	{
		$result = [];

		$order = $payment->getOrder();

		if ($order === null) { return $result; }

		$deliveryPrice = $order->getDeliveryPrice();
		$basket = $order->getBasket();

		if ($basket === null) { return $result; }

		$result['id'] = (string)$order->getId();
		$result['total'] = number_format($payment->getSum(), 2, '.', '');

		/** @var \Bitrix\Sale\BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			if ($basketItem->getFinalPrice() <= 0) { continue; }

			$result['items'][] = [
				'label'     => $basketItem->getField('NAME'),
				'amount'    => number_format($basketItem->getFinalPrice(), 2, '.', '')
			];
		}

		if ($deliveryPrice > 0)
		{
			$result['items'][] = [
				'label'     => Main\Localization\Loc::getMessage('ORDER_DELIVERY'),
				'amount'    => number_format($deliveryPrice, 2, '.', '')
			];
		}

		return $result;
	}

	protected function getOrderRest(Payment $payment): array
	{
		$result = [];

		$order = $payment->getOrder();

		if ($order === null) { return $result; }

		$basket = $order->getBasket();

		if ($basket === null) { return $result; }

		$result['id'] = (string)$payment->getField('PS_INVOICE_ID');
		$result['total'] = $payment->getSum();

		/** @var \Bitrix\Sale\BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			$result['items'][] = [
				'label'     => $basketItem->getField('NAME'),
				'amount'    => $basketItem->getFinalPrice(),
				'productId' => (string)$basketItem->getProductId(),
				'quantity' => [
					'count' => (float)$basketItem->getQuantity()
				]
			];
		}

		return $result;
	}

	public function getParamValue(Payment $payment, $code)
	{
		$code = Config::getLangPrefix() . $code;

		return $this->getBusinessValue($payment, $code);
	}

	/**
	 * @inheritDoc
	 */
	public function getCurrencyList(): array
	{
		return ['RUB'];
	}

	public function refund(Payment $payment, $refundableSum): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		try
		{
			$gateway = $this->wakeUpGateway($payment);
			$gateway->refund();

			$result->setOperationType(PaySystem\ServiceResult::MONEY_LEAVING);
		}
		catch (Main\SystemException $exception)
		{
			$logger = $this->logger($payment);
			$logger->error(...(new Logger\Formatter\Exception($exception))->forLogger());

			$result->addError(new Main\Error($exception->getMessage()));
		}

		return $result;
	}

	/**
	 * @param \Bitrix\Sale\Payment|null $payment
	 *
	 * @return bool
	 */
	public function isTestMode(Payment $payment = null): bool
	{
		return ($this->getBusinessValue($payment, Config::getLangPrefix() . 'TEST_MODE') === 'Y');
	}

	public function getGateway(): Gateway\Base
	{
		$type = $this->getHandlerMode() ?? Gateway\Manager::REST;

		Assert::notNull($type, 'gatewayType');

		return Gateway\Manager::getProvider($type);
	}

	public function wakeUpGateway(Payment $payment) : Gateway\Base
	{
		$gateway = $this->getGateway();
		$params = $this->getParamsBusValue($payment);

		$gateway->setParameters($params);
		$gateway->setPayment($payment);

		return $gateway;
	}

	public function processRequest(Payment $payment, Request $request): ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		try
		{
			$gateway = $this->wakeUpGateway($payment);
			$resultData = $gateway->startPay();

			if (!empty($resultData))
			{
				$fields = [
					'PS_STATUS'         => 'Y',
					'PS_RESPONSE_DATE'  => new Main\Type\DateTime(),
					'PS_STATUS_DESCRIPTION' => $gateway->getId(),
				] + $resultData;

				if (!$payment->isPaid())
				{
					$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
					$result->setPsData($fields);
				}
			}

			$result->setData([
				'state'     => self::STEP_FINISHED,
				'success'   => true,
				'message'   => Main\Localization\Loc::getMessage('SUCCESS'),
			]);
		}
		catch (Secure3dRedirect $exception)
		{
			$result->setData([
				'state'     => self::STEP_3DS,
				'success'   => true,
				'action'    => $exception->getUrl(),
				'params'    => $exception->getParams(),
				'method'    => $exception->getMethod(),
				'termUrl'   => $exception->getTermUrl(),
				'view'      => $exception->getView()
			]);
		}
		catch (Main\SystemException $exception)
		{
			$result->addError(new Main\Error($exception->getMessage()));
		}

		return $result;
	}

	public function getPaymentIdFromRequest(Request $request)
	{
		$externalId = $request->get('externalId');

		if (!empty($externalId)) { return $externalId; }

		$gatewayType = $this->getHandlerMode();

		if (empty($gatewayType)) { return null; }

		$gateway = $this->getGateway();

		$gateway->setParameters($this->getParamsBusValue());

		return $gateway->getPaymentIdFromRequest();
	}

	public function sendResponse(ServiceResult $result, Request $request): void
	{
		$errors = $result->getErrorMessages();
		$response = $result->getData();

		if (!empty($errors))
		{
			$response = [
				'state'     => self::STEP_FAILURE,
				'success'   => false,
				'message'    => $errors
			];
		}

		if ($request->get('accept') === 'json')
		{
			echo Main\Web\Json::encode($response);
		}
		else
		{
			$this->renderResponseHtml($response, $request);
		}
	}

	protected function renderResponseHtml(array $data, Request $request): void
	{
		if ($request->get('backurl') !== null)
		{
			LocalRedirect($request->get('backurl'), true);
			die();
		}

		ShowMessage($data['message']);
	}

	/**
	 * @return array
	 */
	public static function getHandlerModeList(): array
	{
		$result = [];

		if (PaySystemEditPage::isTarget())
		{
			$selected = PaySystemEditPage::selectedGateway();

			if ($selected === null) { return $result; }

			$result += $selected;
		}

		return $result;
	}

	public function getHandlerMode(): ?string
	{
		return $this->service->getField('PS_MODE') ?: null;
	}

	public function isNewWindow(): bool
	{
		return $this->service->getField('NEW_WINDOW') === 'Y';
	}

	public function orderStatusHold(Payment $payment) : string
	{
		return $this->getParamValue($payment, 'STATUS_ORDER_HOLD') ?? '';
	}

	public function orderStatusCapture(Payment $payment) : string
	{
		return $this->getParamValue($payment, 'STATUS_ORDER_CAPTURE') ?? '';
	}

	public function orderStatusRefund(Payment $payment) : string
	{
		return $this->getParamValue($payment, 'STATUS_ORDER_REFUND') ?? '';
	}

	public function orderStatusPartiallyRefund(Payment $payment) : string
	{
		return $this->getParamValue($payment, 'STATUS_ORDER_PARTIALLY_REFUND') ?? '';
	}

	public function orderStatusCancel(Payment $payment) : string
	{
		return $this->getParamValue($payment, 'STATUS_ORDER_CANCEL') ?? '';
	}

	public function logLevel(Payment $payment) : string
	{
		return $this->getParamValue($payment, 'LOG_LEVEL') ?? '';
	}

	public function isAutoPay(Payment $payment) : bool
	{
		return ($this->getParamValue($payment, 'STATUS_ORDER_AUTO_PAY') === 'Y');
	}

	public function getApiKey(Payment $payment) : ?string
	{
		return $this->isTestMode($payment) ? $this->getParamValue($payment, 'MERCHANT_ID') : $this->getParamValue($payment, 'REST_API_KEY');
	}

	public function cancel(Payment $payment) : void
	{
		$logger = $this->logger($payment);

		$this->queryApi(
			$payment,
			Api\Cancel\Request::class,
			Api\Cancel\Response::class,
			$logger
		);

		$logger->info(Main\Localization\Loc::getMessage('PAYMENT_CANCELLED', [
			'#ORDER_ID#' => $payment->getField('PS_INVOICE_ID') ?: $payment->getField('ORDER_ID'),
		]), ['AUDIT' => Logger\Audit::OUTGOING_REQUEST]);
	}

	public function confirm(Payment $payment) : void
	{
		$logger = $this->logger($payment);

		$this->queryApi(
			$payment,
			Api\Capture\Request::class,
			Api\Capture\Response::class,
			$logger
		);

		$logger->info(Main\Localization\Loc::getMessage('PAYMENT_CONFIMED', [
			'#ORDER_ID#' => $payment->getField('PS_INVOICE_ID') ?: $payment->getField('ORDER_ID'),
		]), ['AUDIT' => Logger\Audit::OUTGOING_REQUEST]);
	}

	/**
	 * @param Payment $payment
	 * @param class-string<Api\Request> $requestClass
	 * @param class-string<Api\Reference\Response> $responseClass
	 * @param Psr\Log\LoggerInterface|null $logger
	 */
	protected function queryApi(Payment $payment, string $requestClass, string $responseClass, Psr\Log\LoggerInterface $logger = null) : void
	{
		$request = new $requestClass();

		$apiKey = $this->getApiKey($payment);
		$orderNumber = $payment->getField('PS_INVOICE_ID') ?: $payment->getField('ORDER_ID'); // fallback to ORDER_ID without link

		if ($apiKey === null) { return; }

		$request->setLogger($logger ?? new Logger\NullLogger());
		$request->setApiKey($apiKey);
		$request->setTestMode($this->isTestMode($payment));
		$request->setOrderNumber($orderNumber);

		$data = $request->send();

		$request->buildResponse($data, $responseClass);
	}

	protected function logger(Payment $payment) : Logger\Logger
	{
		$logger = new Logger\Logger();
		$logger->setLevel($this->logLevel($payment));

		return $logger;
	}
}