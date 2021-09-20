<?php

namespace Yandexpay\Pay\GateWay;

use Bitrix\Main\Request;
use Bitrix\Sale\Payment;
use Yandexpay\Pay\Config;
use Yandexpay\Pay\Reference\Concerns\HasMessage;

abstract class Base implements IGateWay
{
	use HasMessage;

	public const TEST_URL = 'test';
	public const ACTIVE_URL = 'active';

	protected static $sort = 0;

	protected $isPaySecure3ds = false;

	/** @var array */
	protected $params = [];

	abstract public function getPaymentIdFromRequest(Request $request): ?int;

	abstract public function refund(Payment $payment, int $refundableSum): void;

	/**
	 * @return array
	 */
	abstract protected function getUrlList(): array;

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

	public function getSort(): int
	{
		return static::$sort;
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

	public function setPaySecure3ds(bool $secure): void
	{
		$this->isPaySecure3ds = $secure;
	}

	public function isPaySecure3ds(): bool
	{
		return $this->isPaySecure3ds;
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
				'NAME'  => static::getMessage('MERCHANT_ID'),
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

	/**
	 * @param string     $action
	 * @param null $replace
	 *
	 * @return string
	 */
	protected function getUrl(string $action, $replace = null): string
	{
		$urlList = $this->getUrlList();

		$result = '';

		if (isset($urlList[$action]))
		{
			$url = $urlList[$action];

			if (is_array($url))
			{
				$result = $url[self::ACTIVE_URL];

				if (isset($url[self::TEST_URL]) && $this->isTestHandlerMode())
				{
					$result = $url[self::TEST_URL];
				}
			}
			else
			{
				$result =  $url;
			}
		}

		if($replace !== null && is_array($replace))
		{
			foreach($replace as $search => $repl)
			{
				$result = str_replace($search, $repl, $result);
			}
		}

		return $result;
	}

	protected function isTestHandlerMode(): bool
	{
		return ($this->getPayParamsKey('YANDEX_PAY_TEST_MODE', true) === 'Y');
	}
}