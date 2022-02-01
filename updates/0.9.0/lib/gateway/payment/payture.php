<?php

namespace YandexPay\Pay\Gateway\Payment;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Main\Web\HttpClient;
use YandexPay\Pay\Gateway;
use YandexPay\Pay\Reference\Concerns;

class Payture extends Gateway\Base
{
	use Concerns\HasMessage;

	protected const STATUS_3DS = '3DS';
	protected const STATUS_SUCCESS = 'True';
	protected const STATUS_FAILED = 'False';

	protected const THREE_DS_VERSION_1 = '1.0';
	protected const THREE_DS_VERSION_2 = '2.1';

	protected $threeDsData = [
		self::THREE_DS_VERSION_1 => [
			'ThreeDSKey' => 'MD',
			'PaReq' => 'PaReq'
		],
		self::THREE_DS_VERSION_2 => [
			'CReq' => 'creq',
			'ThreeDSSessionData' => 'threeDSSessionData'
		]
	];

	public function getId() : string
	{
		return Gateway\Manager::PAYTURE;
	}

	public function getName() : string
	{
		return 'Payture';
	}

	protected function getUrlList() : array
	{
		return [
			'pay' => [
				static::TEST_URL => 'https://sandbox3.payture.com/api/MobilePay',
				static::ACTIVE_URL => 'https://secure.payture.com/api/MobilePay',
			],
			'block' => [
				static::TEST_URL => 'https://sandbox3.payture.com/api/MobileBlock',
				static::ACTIVE_URL => 'https://secure.payture.com/api/MobileBlock',
			],
			'refund' => [
				static::TEST_URL => 'https://sandbox3.payture.com/api/Refund',
				static::ACTIVE_URL => 'https://secure.payture.com/api/Refund',
			],
			'pay3ds' => [
				static::TEST_URL => 'https://sandbox3.payture.com/api/Pay3DS',
				static::ACTIVE_URL => 'https://secure.payture.com/api/Pay3DS',
			]
		];
	}

	protected function getHeaders(string $key = ''): array
	{
		return [
			'Content-type' => 'application/x-www-form-urlencoded'
		];
	}

	protected function getGatewayApiKey(): ?string
	{
		return $this->getParameter('PAYMENT_GATEWAY_API_KEY');
	}

	protected function getGatewayPassword(): ?string
	{
		return $this->getParameter('PAYMENT_GATEWAY_PASSWORD');
	}

	public function getMerchantId() : ?string
	{
		return $this->getParameter('PAYMENT_GATEWAY_API_KEY');
	}

	public function extraParams() : array
	{
		return [
			'PAYMENT_GATEWAY_API_KEY' => [
				'NAME' => static::getMessage('MERCHANT_API_KEY'),
				'GROUP' => $this->getName(),
				'SORT' => 650,
				'INPUT' => [
					'TYPE' => 'STRING',
					'SIZE' => 40,
				],
			],
			'PAYMENT_GATEWAY_PASSWORD' => [
				'NAME' => static::getMessage('MERCHANT_PASSWORD'),
				'GROUP' => $this->getName(),
				'SORT' => 700,
				'INPUT' => [
					'TYPE' => 'STRING',
					'SIZE' => 40,
				],
			]
		];
	}

	public function startPay() : array
	{
		$result = [
			'PS_INVOICE_ID'     => $this->getExternalId(),
			'PS_SUM'            => $this->getPaymentSum()
		];

		if ($this->isSecure3ds())
		{
			$this->createPaySecure();

			return $result;
		}

		$this->createPayment();

		return $result;
	}

	protected function createPayment(): void
	{
		$httpClient = new HttpClient();

		$data = $this->buildData();

		$requestUrl = $this->getUrl('pay');

		$this->setHeaders($httpClient);

		$httpClient->post($requestUrl, $data);

		$resultData = $this->convertResultData($httpClient->getResult());

		$this->checkResult($resultData, $httpClient->getStatus());
	}

	protected function createPaySecure(): void
	{
		$httpClient = new HttpClient();

		$data = [
			'Key'       => $this->getGatewayApiKey(),
			'OrderId'   => $this->getExternalId(),
			'PaRes'     => $this->request->get('PaRes') ?? $this->request->get('cres') ?? $this->request->get('CRes')
		];

		$url = $this->getUrl('pay3ds');

		$this->setHeaders($httpClient);

		$httpClient->post($url, $data);

		$resultData = $this->convertResultData($httpClient->getResult());

		$this->checkResult($resultData, $httpClient->getStatus());
	}

	protected function convertResultData($data): array
	{
		$result = [];

		$resultXml = new \SimpleXMLElement($data);

		foreach ($resultXml->attributes() as $code => $value)
		{
			$result[$code] = (string)$value;
		}

		return $result;
	}

	protected function buildData(): array
	{
		$customFields = [
			'ChallengeNotificationUrl'  => $this->getRedirectUrl(),
			'BrowserData'               => $this->getBrowserData()
		];

		return [
			'PayToken'      => $this->getYandexToken(),
			'OrderId'       => $this->getExternalId(),
			'Amount'        => $this->getPaymentAmount(),
			'Key'           => $this->getGatewayApiKey(),
			'CustomFields'  => $this->buildCustomFields($customFields)
		];
	}

	protected function getBrowserData() : string
	{
		return base64_encode(Main\Web\Json::encode([
			'AcceptHeader'              => 'application/x-www-form-urlencoded',
			'ColorDepth'                => 'TWENTY_FOUR_BITS',
			'Ip'                        => $this->server->get('REMOTE_ADDR'),
			'Language'                  => 'RU',
			'ScreenHeight'              => 1080,
			'ScreenWidth'               => 1920,
			'WindowHeight'              => 1050,
			'WindowWidth'               => 1920,
			'Timezone'                  => '180',
			'UserAgent'                 => $this->server->get('HTTP_USER_AGENT'),
			'JavaEnabled'               => true
		]));
	}

	protected function buildCustomFields(array $params, string $separator = ';') : string
	{
		$paramsJoined = [];

		foreach($params as $param => $value)
		{
			$paramsJoined[] = $param . '=' . $value;
		}

		return implode($separator, $paramsJoined);
	}

	public function getPaymentIdFromRequest() : ?int
	{
		return $this->request->get('paymentId');
	}

	public function refund(): void
	{
		$httpClient = new HttpClient();
		$url = $this->getUrl('refund');

		$data = [
			'Key'       => $this->getGatewayApiKey(),
			'Password'  => $this->getGatewayPassword(),
			'OrderId'   => $this->getPaymentField('PS_INVOICE_ID'),
			'Amount'    => $this->getPaymentAmount()
		];

		$this->setHeaders($httpClient);

		$httpClient->post($url, $data);

		$result = $this->convertResultData($httpClient->getResult());

		$this->checkResult($result, $httpClient->getStatus());
	}

	protected function checkResult(array $resultData, int $status): void
	{
		if (empty($resultData))
		{
			throw new Main\SystemException('GOT EMPTY RESULT WITH STATUS = ' . $status);
		}

		if ($resultData['Success'] === self::STATUS_FAILED && isset($resultData['ErrCode']))
		{
			throw new Main\SystemException(static::getMessage($resultData['ErrCode']));
		}

		if ($resultData['Success'] === self::STATUS_3DS)
		{
			$params = $this->buildParamsForSecure($resultData);
			$isTermUrl = ($resultData['ThreeDSVersion'] === self::THREE_DS_VERSION_1);

			throw new \YandexPay\Pay\Exceptions\Secure3dRedirect(
				$resultData['ACSUrl'], $params, $isTermUrl
			);
		}
	}

	protected function buildParamsForSecure(array $data) : array
	{
		$result = [];

		$mapOptions = $this->threeDsData[$data['ThreeDSVersion']];

		if ($mapOptions !== null)
		{
			$options = array_intersect_key($data, $mapOptions);

			foreach ($mapOptions as $code => $name)
			{
				if (!isset($options[$code])) { continue; }

				$result[$name] = $options[$code];
			}
		}

		$result['termUrl'] = $this->getRedirectUrl();

		return $result;
	}

	protected function isSecure3ds() : bool
	{
		return $this->request->get('secure3ds') !== null;
	}
}