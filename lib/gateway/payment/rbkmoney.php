<?php

namespace YandexPay\Pay\GateWay\Payment;

use Bitrix\Main;
use Bitrix\Main\Request;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\Payment;
use YandexPay\Pay\Exceptions\Secure3dRedirect;
use YandexPay\Pay\GateWay\Base;
use YandexPay\Pay\Reference\Concerns\HasMessage;

class Rbkmoney extends Base
{
	use HasMessage;

	protected static $sort = 200;

	protected const STATUS_PAID = 'processed';
	protected const STATUS_FAILED = 'failed';
	protected const STATUS_REFUNDED = 'refunded';

	protected const TYPE_EVENT_3DS = 'PaymentInteractionRequested';
	protected const TYPE_EVENT_CHANGED = 'PaymentStatusChanged';

	protected const PAYMENT_PAYER_TYPE = 'PaymentResourcePayer';
	protected const PAYMENT_FLOW_TYPE = 'PaymentFlowInstant';

	protected const RESOURCE_PROVIDER = 'YandexPay';
	protected const RESOURCE_PAYMENT_TYPE = 'TokenizedCardData';

	protected const WEBHOOK_TYPE_PROCESSED = 'PaymentProcessed';

	public function getId() : string
	{
		return 'rbkmoney';
	}

	public function getName() : string
	{
		return 'Rbk money';
	}

	protected function getUrlList() : array
	{
		return [
			'createResource'    => 'https://api.rbk.money/v2/processing/payment-resources',
			'createInvoice'     => 'https://api.rbk.money/v2/processing/invoices',
			'createToken'       => 'https://api.rbk.money/v2/processing/invoices/#INVOICE_ID#/access-tokens',
			'createPay'         => 'https://api.rbk.money/v2/processing/invoices/#INVOICE_ID#/payments',
			'getInvoice'        => 'https://api.rbk.money/v2/processing/invoices?externalID=#EXTERNAL_ID#',
			'getPayment'        => 'https://api.rbk.money/v2/processing/payments?externalID=#EXTERNAL_ID#',
			'invoiceEvents'     => 'https://api.rbk.money/v2/processing/invoices/#INVOICE_ID#/events?limit=100',
			'refund'            => 'https://api.rbk.money/v2/processing/invoices/#INVOICE_ID#/payments/#PAYMENT_ID#/refunds',
		];
	}

	public function extraParams(string $code = '') : array
	{
		return [
			$code . '_PAYMENT_GATEWAY_SHOP_ID' => [
				'NAME' => static::getMessage('MERCHANT_SHOP_ID'),
				'GROUP' => $this->getName(),
				'SORT' => 650,
			],
			$code . '_PAYMENT_GATEWAY_API_KEY' => [
				'NAME' => static::getMessage('MERCHANT_API_KEY'),
				'GROUP' => $this->getName(),
				'SORT' => 700,
				'INPUT' => [
					'TYPE' => 'STRING',
					'SIZE' => null,
				]
			],
			$code . '_WEBHOOK_PROCESSED_KEY' => [
				'NAME' => static::getMessage('WEBHOOK_PROCESSED_KEY'),
				'GROUP'=> $this->getName(),
				'SORT' => 750,
				'INPUT' => [
					'TYPE' => 'STRING',
					'SIZE' => null,
					'MULTILINE' => 'Y',
					'ROWS' => 15,
					'COLS' => 65
				]
			]
		];
	}

	protected function getHeaders(string $apiKey): array
	{
		return [
			'Authorization' => 'Bearer ' . $apiKey,
			'Content-type'  => 'application/json; charset=utf-8',
			'X-Request-ID'  =>  uniqid('', true),
		];
	}

	public function getPaymentIdFromRequest(Request $request) : ?int
	{
		$result = null;

		$contentSignature = $request->getServer()->get('HTTP_CONTENT_SIGNATURE');

		if ($contentSignature === null) { return null; }

		$signature = $this->getSignatureFromHeader($contentSignature);

		if (empty($signature)) { return null; }

		$decodedSignature = $this->urlSafeBase64decode($signature);

		$content = $this->readFromStream();

		$webhookPublicKey = $this->getPayParamsKey('WEBHOOK_PROCESSED_KEY');

		if ($this->isVerifySignature($content, $decodedSignature, $webhookPublicKey))
		{
			$content = $this->convertResultData($content);
			$result = (int)$content['payment']['metadata']['externalId'];
		}

		return $result;
	}

	protected function getSignatureFromHeader($contentSignature): string
	{
		return preg_replace("/alg=(\S+);\sdigest=/", '', $contentSignature);
	}

	protected function urlSafeBase64decode($signature)
	{
		return base64_decode(strtr($signature, '-_,', '+/='));
	}

	protected function isVerifySignature($content, $signature, $publicKey): bool
	{
		if (empty($content) || empty($signature) || empty($publicKey)) { return false; }

		$publicKeyId = openssl_get_publickey($publicKey);

		if (empty($publicKeyId)) { return false; }

		$verify = openssl_verify($content, $signature, $publicKeyId, OPENSSL_ALGO_SHA256);

		return ($verify === 1);
	}

	protected function processWebHook(Payment $payment): array
	{
		$result = [];
		$content = $this->convertResultData($this->readFromStream());

		$webhook = $content['eventType'];

		if ($webhook !== self::WEBHOOK_TYPE_PROCESSED) { return $result; }

		$shopId = $this->getPayParamsKey('PAYMENT_GATEWAY_SHOP_ID');
		$orderId = $payment->getOrderId();

		if (
			$content['payment']['status'] === self::STATUS_PAID
			&& (int)$orderId === (int)$content['payment']['metadata']['orderId']
			&& $content['invoice']['shopID'] === $shopId
		)
		{
			$result = [
				'PS_INVOICE_ID'     => $content['invoice']['id'],
				'PS_STATUS_CODE'    => $content['payment']['status'],
				'PS_SUM'            => $payment->getSum()
			];
		}

		return $result;
	}

	public function startPay(Payment $payment, Request $request) : array
	{
		$fields = $this->processWebHook($payment);

		if (!empty($fields)) { return $fields; }

		[$invoiceId, $invoiceToken] = $this->buildInvoice($payment, $request);

		$resource = $this->createPaymentResource($payment, $request, $invoiceToken);

		$this->createPayment($payment, $request, $invoiceId, $invoiceToken, $resource);

		$this->checkInvoiceEvents($invoiceId);

		return $fields;
	}

	protected function getInvoiceByExternalId(string $externalId): array
	{
		$result = [];

		$apiKey = $this->getPayParamsKey('PAYMENT_GATEWAY_API_KEY');

		$httpClient = new HttpClient();

		$url = $this->getUrl('getInvoice', ['#EXTERNAL_ID#' => $externalId]);

		$httpClient->setHeaders($this->getHeaders($apiKey));

		$httpClient->get($url);

		$resultData = $this->convertResultData($httpClient->getResult());

		$this->checkResult($resultData, $httpClient->getStatus());

		if ($httpClient->getStatus() === 200)
		{
			$invoiceId = (string)$resultData['id'];
			$token = $this->createAccessToken($invoiceId);

			$result = [$invoiceId, $token];
		}

		return $result;
	}

	protected function createAccessToken(string $invoiceId): string
	{
		$apiKey = $this->getPayParamsKey('PAYMENT_GATEWAY_API_KEY');

		$url = $this->getUrl('createToken', ['#INVOICE_ID#' => $invoiceId]);

		$httpClient = new HttpClient();

		$httpClient->setHeaders($this->getHeaders($apiKey));

		$httpClient->post($url);

		$result = $this->convertResultData($httpClient->getResult());

		$this->checkResult($result, $httpClient->getStatus());

		return $result['payload'];
	}

	protected function buildInvoice(Payment $payment, Request $request): array
	{
		$externalId = (string)$payment->getId();

		$invoice = $this->getInvoiceByExternalId($externalId);

		if (!empty($invoice)) { return $invoice; }

		return $this->createInvoice($payment, $request);
	}

	protected function createInvoice(Payment $payment, Request $request): array
	{
		$apiKey = $this->getPayParamsKey('PAYMENT_GATEWAY_API_KEY');

		$httpClient = new HttpClient();

		$url = $this->getUrl('createInvoice');

		$data = $this->buildDataInvoice($payment, $request);

		$httpClient->setHeaders($this->getHeaders($apiKey));

		$httpClient->post($url, $data);

		$result = $this->convertResultData($httpClient->getResult());

		$this->checkResult($result, $httpClient->getStatus());

		return [$result['invoice']['id'], $result['invoiceAccessToken']['payload']];
	}

	protected function buildDataInvoice(Payment $payment, Request $request): string
	{
		$cart = [];

		$order = $payment->getOrder();
		$basket = $order->getBasket();
		$deliveryPrice = $order->getDeliveryPrice();

		/** @var \Bitrix\Sale\BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			$cart[] = [
				'product'   => $basketItem->getField('NAME'),
				'quantity'  => $basketItem->getQuantity(),
				'price'     => round($basketItem->getPrice() * 100),
			];
		}

		if ($deliveryPrice > 0)
		{
			$cart[] = [
				'product'   => 'delivery',
				'quantity'  => 1,
				'price'     => round($deliveryPrice * 100)
			];
		}

		return Main\Web\Json::encode([
			'shopID'    => $this->getPayParamsKey('PAYMENT_GATEWAY_SHOP_ID'),
			'dueDate'   => $this->getDueDate(),
			'currency'  => $payment->getField('CURRENCY'),
			'product'   => static::getMessage('PRODUCT_NAME', ['#ORDER_ID#' => $order->getId()]),
			'cart'      => $cart,
			'metadata'  => [
				'externalId'    => $request->get('externalId'),
				'paySystemId'   => $request->get('paySystemId'),
				'orderId'       => $payment->getOrderId()
			],
			'amount'    => round($payment->getSum() * 100),
			'externalID'=> (string)$request->get('externalId')
		]);
	}

	protected function getDueDate(): string
	{
		$date = new Main\Type\DateTime();

		return $date->add('+1 day')->format('Y-m-d\TH:i:s.u\Z');
	}

	protected function createPaymentResource(Payment $payment, Request $request, string $token): array
	{
		$data = $this->buildDataResource($payment, $request);

		$httpClient = new HttpClient();

		$requestUrl = $this->getUrl('createResource');

		$httpClient->setHeaders($this->getHeaders($token));

		$httpClient->post($requestUrl, $data);

		$result = $this->convertResultData($httpClient->getResult());

		$this->checkResult($result, $httpClient->getStatus());

		return $result;
	}

	protected function buildDataResource(Payment $payment, Request $request): string
	{
		$merchantId = $this->getPayParamsKey('PAYMENT_GATEWAY_MERCHANT_ID');
		$yandexData = $request->get('yandexData');
		$externalId = $request->get('externalId');

		$userAgent = $request->getServer()->get('HTTP_USER_AGENT');

		return Main\Web\Json::encode([
			'paymentTool' => [
				'provider'          => self::RESOURCE_PROVIDER,
				'paymentToolType'   => self::RESOURCE_PAYMENT_TYPE,
				'gatewayMerchantID' => $merchantId,
				'paymentToken'      => $yandexData,
			],
			'clientInfo' => [
				'fingerprint'   => $userAgent,
			],
			'externalID' => (string)$externalId,
		]);
	}

	protected function createPayment(Payment $payment, Request $request, string $invoiceId, string $token, array $resourceData): void
	{
		$url = $this->getUrl('createPay', ['#INVOICE_ID#' => $invoiceId]);

		$data = $this->buildDataPayment($payment, $request, $resourceData);

		$httpClient = new HttpClient();

		$httpClient->setHeaders($this->getHeaders($token));

		$httpClient->post($url, $data);

		$resultData = $this->convertResultData($httpClient->getResult());

		$this->checkResult($resultData, $httpClient->getStatus());
	}

	protected function buildDataPayment(Payment $payment, Request $request, array $resourceData): string
	{
		$externalId = (string)$request->get('externalId');
		$order = $payment->getOrder();

		$propertyCollection = $order->getPropertyCollection();

		$contactInfo = [];
		$email = $propertyCollection->getUserEmail();
		$phone = $propertyCollection->getPhone();

		if ($email !== null)
		{
			$contactInfo['email'] = $email->getValue();
		}

		if ($phone !== null)
		{
			$contactInfo['phoneNumber'] = $phone->getValue();
		}

		return Main\Web\Json::encode([
			'externalID'    => $externalId,
			'flow'          => [
				'type' => self::PAYMENT_FLOW_TYPE,
			],
			'payer'         => [
				'paymentToolToken'  => $resourceData['paymentToolToken'],
				'paymentSession'    => $resourceData['paymentSession'],
				'payerType'         => self::PAYMENT_PAYER_TYPE,
				'contactInfo'       => $contactInfo,
			],
			'metadata'  => [
				'externalId'    => $externalId,
				'paySystemId'   => $request->get('paySystemId'),
				'orderId'       => $payment->getOrderId()
			],
		], JSON_FORCE_OBJECT);

	}

	protected function checkInvoiceEvents(string $invoiceId): void
	{
		for ($seconds = 0; $seconds < 5; $seconds++)
		{
			$httpClient = new HttpClient();

			$apiKey = $this->getPayParamsKey('PAYMENT_GATEWAY_API_KEY');

			$url = $this->getUrl('invoiceEvents', ['#INVOICE_ID#' => $invoiceId]);

			$httpClient->setHeaders($this->getHeaders($apiKey));

			$httpClient->get($url);

			$resultData = $this->convertResultData($httpClient->getResult());

			$this->checkResult($resultData, $httpClient->getStatus());

			if ($this->checkStatusPay($resultData)) { break; }

			sleep(1);
		}
	}

	protected function checkResult(array $resultData, int $status): void
	{
		if (empty($resultData))
		{
			throw new Main\SystemException('GOT EMPTY RESULT WITH STATUS = ' . $status);
		}

		if ($status === 400)
		{
			throw new Main\SystemException(static::getMessage($resultData['code']) . ' - ' . $resultData['message']);
		}

		if ($status === 401)
		{
			throw new Main\SystemException('Authorization error');
		}

		if ($status === 409)
		{
			throw new Main\SystemException($resultData['externalID'] . ': '. $resultData['message']);
		}

		if (isset($resultData['error']) && !empty($resultData['error']))
		{
			throw new Main\SystemException(static::getMessage($resultData['error']['code']) . ': '. $resultData['error']['subError']);
		}
	}

	protected function checkStatusPay(array $resultData): bool
	{
		$result = false;

		$changes = array_merge(... array_column($resultData, 'changes'));

		if (empty($changes)) { return $result; }

		foreach ($changes as $change)
		{
			if ($change['changeType'] === self::TYPE_EVENT_3DS)
			{
				throw new Secure3dRedirect($change['userInteraction']['uriTemplate'], $change['userInteraction'], $change['paymentID']);
			}

			if (
				$change['changeType'] === self::TYPE_EVENT_CHANGED
				&& $change['status'] === self::STATUS_FAILED
			)
			{
				throw new Main\SystemException(self::getMessage('FAILED_STATUS'));
			}

			if (
				$change['changeType'] === self::TYPE_EVENT_CHANGED
				&& $change['status'] === self::STATUS_REFUNDED
			)
			{
				throw new Main\SystemException(self::getMessage('REFUNDED_STATUS'));
			}

			if (
				$change['changeType'] === self::TYPE_EVENT_CHANGED
				&& $change['status'] === self::STATUS_PAID
			)
			{
				$result = true;
			}
		}

		return $result;
	}

	protected function convertResultData(string $data): array
	{
		return Main\Web\Json::decode($data);
	}

	protected function getPaymentIdByExternalId(string $id): string
	{
		$httpClient = new HttpClient();

		$apiKey = $this->getPayParamsKey('PAYMENT_GATEWAY_API_KEY');

		$url = $this->getUrl('getPayment', ['#EXTERNAL_ID#' => $id]);

		$httpClient->setHeaders($this->getHeaders($apiKey));

		$httpClient->get($url);

		if ($httpClient->getStatus() === 500)
		{
			throw new Main\SystemException('Internal Server Error');
		}

		$result = $this->convertResultData($httpClient->getResult());

		$this->checkResult($result, $httpClient->getStatus());

		return (string)$result['id'];
	}

	public function refund(Payment $payment, $refundableSum): void
	{
		$externalId = (string)$payment->getId();

		$apiKey = $this->getPayParamsKey('PAYMENT_GATEWAY_API_KEY');

		$invoiceId = $payment->getField('PS_INVOICE_ID');

		$paymentId = $this->getPaymentIdByExternalId($externalId);

		$httpClient = new HttpClient();

		$url = $this->getUrl('refund', ['#INVOICE_ID#' => $invoiceId, '#PAYMENT_ID#' => $paymentId]);

		$data = $this->buildDataRefund($payment);

		$httpClient->setHeaders($this->getHeaders($apiKey));

		$httpClient->post($url, $data);

		$result = $this->convertResultData($httpClient->getResult());

		$this->checkResult($result, $httpClient->getStatus());
	}

	protected function buildDataRefund(Payment $payment)
	{
		$application = Main\Application::getInstance();
		$index = $payment->getInternalIndex();
		$comment = '';

		if ($application !== null)
		{
			$request = $application->getContext()->getRequest()->toArray();
			$comment = $request['data']['PAY_RETURN_COMMENT_' . $index];
		}

		return Main\Web\Json::encode([
			'externalID'    => (string)$payment->getId(),
			'amount'        => round($payment->getSum() * 100),
			'currency'      => $payment->getField('CURRENCY'),
			'reason'        => \Bitrix\Main\Text\Encoding::convertEncodingToCurrent($comment)
		]);
	}
}