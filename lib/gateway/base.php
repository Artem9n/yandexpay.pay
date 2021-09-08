<?php

namespace Yandexpay\Pay\GateWay;

use Bitrix\Main\Request;
use Bitrix\Sale\Payment;
use Yandexpay\Pay\Config;
use Yandexpay\Pay\Reference\Concerns\HasMessage;

abstract class Base implements IGateWay
{
	use HasMessage;

	/** @var array */
	protected $params = [];

	abstract public function isMyResponse(Request $request, int $paySystemId): bool;

	abstract public function getPaymentIdFromRequest(Request $request): ?int;

	abstract public function refund(Payment $payment, int $refundableSum): void;

	public static function getClassName(): string
	{
		return '\\' . static::class;
	}

	public function getId() : string
	{
		return '';
	}

	public function getName() : string
	{
		return  '';
	}

	public function getDescription(): string
	{
		return static::getMessage('DESCRIPTION');
	}

	public function extraParams(string $code = ''): array
	{
		return [];
	}

	public function setPayParams(array $params = []): void
	{
		$this->params = $params;
	}

	public function getPayParams(): array
	{
		return $this->params;
	}

	public function getPayParamsKey(string $key, $isStrict = false)
	{
		$prefix = static::getPrefix();
		$key = !$isStrict ? $prefix . $this->getId() . '_' . $key : $key;

		return $this->params[$key] ?? null;
	}

	public function getParams() : array
	{
		$prefix = static::getPrefix();
		$code = $prefix . $this->getId();

		$extraParams = $this->extraParams($code);

		return [
			$code .'_PAYMENT_GATEWAY_MERCHANT_ID' => [
				'NAME'  => static::getMessage('MERCHANT_NAME'),
				'GROUP' => $this->getName(),
				'SORT'  => 600
			],
		] + $extraParams;
	}

	protected static function getPrefix(): string
	{
		return Config::getLangPrefix();
	}

	public function startPay(Payment $payment, Request $request) : array
	{
		return [];
	}

	/**
	 * @return bool|string
	 */
	protected function readFromStream()
	{
		return file_get_contents("php://input");
	}
}