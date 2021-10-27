<?php

namespace YandexPay\Pay\Gateway;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Config;
use YandexPay\Pay\Reference\Concerns\HasMessage;

abstract class Base implements IGateway, Main\Type\IRequestFilter
{
	use HasMessage;

	public const TEST_URL = 'test';
	public const ACTIVE_URL = 'active';

	/** @var int */
	protected $sort;
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

	public function __construct(Sale\Payment $payment = null, Main\Request $request = null)
	{
		$this->payment = $payment;
		$this->server = Main\Context::getCurrent()->getServer();
		$this->request = $request ?? Main\Context::getCurrent()->getRequest();
	}

	abstract public function getPaymentIdFromRequest(): ?int;

	abstract public function refund(): void;

	abstract protected function getUrlList(): array;

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
		return $this->sort;
	}

	public function getDescription(): string
	{
		return static::getMessage('DESCRIPTION');
	}

	public function extraParams(string $code = ''): array
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

	public function getMerchantId(): string
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

	protected function readFromStream(): void
	{
		$this->request->addFilter($this);
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
		return $this->request->get('yandexData') ?? [];
	}

	protected function getYandexToken() : string
	{
		$yandexData = $this->getYandexData();

		return $yandexData['token'] ?? '';
	}

	public function filter(array $values): array
	{
		try
		{
			$rawInput = file_get_contents('php://input');
			$postData = Main\Web\Json::decode($rawInput);

			$result = [
				'post' => $postData,
			];
		}
		catch (\Exception $exception)
		{
			$result = [];
		}

		return $result;
	}

	protected function getRedirectUrl() : string
	{
		$params = [
			'paymentId' => $this->getPaymentId(),
			'backurl'   => $_SESSION['yabackurl']
		];

		return $this->getParameter('YANDEX_PAY_NOTIFY_URL', true) . '?' . http_build_query($params);
	}

	protected function getHeaders(string $key = '') : array
	{
		return [];
	}

	protected function setHeaders(Main\Web\HttpClient $httpClient, string $key = '') : void
	{
		foreach ($this->getHeaders($key) as $name => $value)
		{
			$httpClient->setHeader($name, $value);
		}
	}
}