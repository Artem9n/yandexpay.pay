<?php

namespace YandexPay\Pay\Gateway;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Config;
use YandexPay\Pay\Reference\Concerns\HasMessage;

abstract class Base implements IGateway
{
	use HasMessage;

	public const TEST_URL = 'test';
	public const ACTIVE_URL = 'active';

	/** @var array */
	protected $params = [];
	/** @var Sale\Payment */
	protected $payment;
	/** @var Main\Request */
	protected $request;
	/** @var Main\Server */
	protected $server;
	/** @var string */
	protected $externalId;

	public function __construct()
	{
		$this->server = Main\Context::getCurrent()->getServer();
		$this->request = Main\Context::getCurrent()->getRequest();
	}

	abstract public function getPaymentIdFromRequest(): ?int;

	abstract public function refund(): void;

	abstract protected function getUrlList(): array;

	public function getGatewayId() : string
	{
		return $this->getId();
	}

	public function getId() : string
	{
		return '';
	}

	public function setPayment(Sale\Payment $payment) : void
	{
		$this->payment = $payment;
	}

	public function getName() : string
	{
		return  '';
	}

	public function getDescription(): string
	{
		return static::getMessage('DESCRIPTION');
	}

	public function extraParams(): array
	{
		return [];
	}

	public function setParameters(array $params = []): void
	{
		$this->params = $params;
	}

	public function getParameters(): array
	{
		return $this->params;
	}

	public function getParameter(string $name, $isStrict = false)
	{
		$prefix = static::getPrefix();
		$name = !$isStrict ? $prefix . $this->getId() . '_' . $name : $name;

		return $this->params[$name] ?? null;
	}

	public function getParams() : array
	{
		if ($this->getGatewayId() === Manager::OTHER) { return []; }

		$prefix = static::getPrefix();
		$code = $prefix . $this->getId();

		$extraParams = $this->extraParams();
		$result = [];

		if (!empty($extraParams))
		{
			foreach ($extraParams as $key => $value)
			{
				$value += [
					'DESCRIPTION' => self::getMessage('FIELD_DESCRIPTION', [
						'#GATEWAY#' => $this->getName()
					]),
				];

				$result[$code . '_' . $key] = $value;
			}
		}

		return $result;
	}

	public function getMerchantId(): ?string
	{
		return $this->getParameter('PAYMENT_GATEWAY_MERCHANT_ID');
	}

	protected static function getPrefix(): string
	{
		return Config::getLangPrefix();
	}

	public function startPay() : array
	{
		return [];
	}

	/**
	 * @param string     $action
	 * @param array|null $replace
	 *
	 * @return string
	 */
	protected function getUrl(string $action, array $replace = null): string
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

		if(is_array($replace))
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
		return ($this->getParameter('YANDEX_PAY_TEST_MODE', true) === 'Y');
	}

	protected function getPaymentId() : string
	{
		return (string)$this->payment->getId();
	}

	protected function getOrderId() : string
	{
		return (string)$this->payment->getOrderId();
	}

	protected function getPaymentSum() : float
	{
		return $this->payment->getSum();
	}

	protected function getPaymentAmount() : float
	{
		return round($this->getPaymentSum() * 100);
	}

	protected function getPaymentField(string $name) : ?string
	{
		return $this->payment->getField($name);
	}

	protected function createExternalId() : string
	{
		return md5(serialize([
			$this->server->getServerName(),
			$this->payment->getOrder()->getUserId(),
			$this->payment->getOrder()->getDateInsert(),
			$this->getPaymentSum(),
			$this->getOrderId(),
			$this->getPaymentId()
		]));
	}

	protected function getExternalId() : ?string
	{
		if ($this->externalId === null)
		{
			$this->externalId = $this->createExternalId();
		}

		return $this->externalId;
	}

	protected function getYandexData() : array
	{
		return (array)$this->request->get('yandexData');
	}

	protected function getYandexToken() : string
	{
		$yandexData = $this->getYandexData();

		return $yandexData['token'] ?? '';
	}

	protected function getRedirectUrl(array $extraParams = []) : string
	{
		$params = [
			'paymentId' => $this->getPaymentId(),
			'paySystemId' => $this->payment->getPaymentSystemId(),
			'backurl'   => $_SESSION['yabackurl'] ?? $_SESSION['yabehaviorbackurl'],
			'secure3ds' => 'Y'
		];

		$extraParams += $params;

		/*$secure = [
			'secure3ds' => static::generateParams($extraParams)
		];*/

		return $this->getParameter('YANDEX_PAY_NOTIFY_URL', true) . '?' . http_build_query($extraParams);
	}

	protected function getHeaders(string $key = '') : array
	{
		return [];
	}

	public static function generateParams(array $params) : string
	{
		return base64_encode(Main\Web\Json::encode($params));
	}

	public static function parseParams(string $params) : array
	{
		return Main\Web\Json::decode(base64_decode($params));
	}

	protected function setHeaders(Main\Web\HttpClient $httpClient, string $key = '') : void
	{
		foreach ($this->getHeaders($key) as $name => $value)
		{
			$httpClient->setHeader($name, $value);
		}
	}
}