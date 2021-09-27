<?php

namespace YandexPay\Pay\GateWay\Payment;

use Bitrix\Main;
use Bitrix\Main\Request;
use Bitrix\Sale\Payment;
use Bitrix\Main\Web\HttpClient;
use YandexPay\Pay\GateWay\Base;
use YandexPay\Pay\Reference\Concerns\HasMessage;

class Payture extends Base
{
	use HasMessage;

	protected const STATUS_3DS = '3DS';
	protected const STATUS_SUCCESS = 'True';
	protected const STATUS_FAILED = 'False';

	/** @var int  */
	protected $sort = 100;

	public function getId() : string
	{
		return 'payture';
	}

	public function getName() : string
	{
		return 'Payture';
	}

	protected function getUrlList() : array
	{
		return [
			'pay'       => 'https://sandbox3.payture.com/api/MobilePay',
			'refund'    => 'https://sandbox3.payture.com/api/Refund',
			'pay3ds'    => 'https://sandbox3.payture.com/api/Pay3DS'
		];
	}

	protected function getHeaders(): array
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

	public function extraParams(string $code = '') : array
	{
		return [
			$code . '_PAYMENT_GATEWAY_API_KEY' => [
				'NAME' => static::getMessage('MERCHANT_API_KEY'),
				'GROUP' => $this->getName(),
				'SORT' => 650
			],
			$code . '_PAYMENT_GATEWAY_PASSWORD' => [
				'NAME' => static::getMessage('MERCHANT_PASSWORD'),
				'GROUP' => $this->getName(),
				'SORT' => 700
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

		$httpClient->setHeaders($this->getHeaders());

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
			'PaRes'     => $this->request->get('PaRes')
		];

		$url = $this->getUrl('pay3ds');

		$httpClient->setHeaders($this->getHeaders());

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
		$metaData = [
			'externalId'    => $this->getPaymentId(),
			'orderId'       => $this->getOrderId()
		];

		return [
			'PayToken'      => $this->getYandexToken(),
			'OrderId'       => $this->getExternalId(),
			'Amount'        => $this->getPaymentAmount(),
			'Key'           => $this->getGatewayApiKey(),
			'CustomFields'  => http_build_query($metaData, '', ';')
		];
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

		$httpClient->setHeaders($this->getHeaders());

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
			throw new \YandexPay\Pay\Exceptions\Secure3dRedirect(
				$resultData['ACSUrl'], [
					'MD'    => $resultData['ThreeDSKey'],
					'PaReq' => $resultData['PaReq']
				], true
			);
		}
	}

	protected function isSecure3ds() : bool
	{
		return ($this->request->get('MD') !== null && $this->request->get('PaRes') !== null);
	}
}