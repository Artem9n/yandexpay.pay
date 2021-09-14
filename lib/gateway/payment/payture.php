<?php

namespace Yandexpay\Pay\GateWay\Payment;

use Bitrix\Main;
use Bitrix\Main\Request;
use Bitrix\Sale\Payment;
use Bitrix\Main\Web\HttpClient;
use Yandexpay\Pay\GateWay\Base;
use Yandexpay\Pay\Reference\Concerns\HasMessage;

class Payture extends Base
{
	use HasMessage;

	protected static $sort = 100;

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
			'refund'    => 'https://sandbox3.payture.com/api/Refund'
		];
	}

	protected function getHeaders(): array
	{
		return [
			'Content-type' => 'application/x-www-form-urlencoded'
		];
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

	public function startPay(Payment $payment, Request $request) : array
	{
		$httpClient = new HttpClient();

		$data = $this->buildData($payment, $request);

		$requestUrl = $this->getUrl('pay');

		$httpClient->setHeaders($this->getHeaders());

		$httpClient->post($requestUrl, $data);

		$resultData = $this->convertResultData($httpClient->getResult());

		$this->checkResult($resultData, $httpClient->getStatus());

		return [
			'PS_INVOICE_ID'     => $payment->getOrderId(),
			'PS_SUM'            => $payment->getSum()
		];
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

	protected function buildData(Payment $payment, Request $request): array
	{
		$requestData = $request->toArray();
		$apiKey = $this->getPayParamsKey('PAYMENT_GATEWAY_API_KEY');

		return [
			'PayToken'  => $requestData['yandexData']['token'],
			'OrderId'   => $payment->getOrderId(),
			'Amount'    => round($payment->getSum() * 100),
			'Key'       => $apiKey
		];
	}

	public function isMyResponse(Request $request, int $paySystemId) : bool
	{
		// TODO: Implement isMyResponse() method.
	}

	public function getPaymentIdFromRequest(Request $request) : ?int
	{
		// TODO: Implement getPaymentIdFromRequest() method.
	}

	public function refund(Payment $payment, $refundableSum): void
	{
		$httpClient = new HttpClient();
		$url = $this->getUrl('refund');

		$apiKey = $this->getPayParamsKey('PAYMENT_GATEWAY_API_KEY');
		$password = $this->getPayParamsKey('PAYMENT_GATEWAY_PASSWORD');

		$data = [
			'Key'       => $apiKey,
			'Password'  => $password,
			'OrderId'   => $payment->getOrderId(),
			'Amount'    => round($payment->getSum() * 100)
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

		if ($resultData['Success'] === 'False' && isset($resultData['ErrCode']))
		{
			throw new Main\SystemException(static::getMessage($resultData['ErrCode'], ['#ORDER_ID#' => $resultData['OrderId']]));
		}

		if ($resultData['Success'] === '3DS')
		{
			throw new \Yandexpay\Pay\Exceptions\Secure3dRedirect(
				$resultData['ACSUrl'],
				$resultData['PaReq'],
				$resultData['ThreeDSKey']
			);
		}
	}
}