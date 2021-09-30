<?php

namespace YandexPay\Pay\Exceptions;

use Bitrix\Main;

class Secure3dRedirect extends Main\SystemException
{
	private $url;
	private $params;
	private $method;
	private $termUrl;

	public function __construct(string $url, array $params, bool $termUrl = false, string $method = 'POST')
	{
		parent::__construct('3ds redirect');

		$this->url = $url;
		$this->params = $params;
		$this->method = $method;
		$this->termUrl = $termUrl;
	}

	public function getUrl() : string
	{
		return $this->url;
	}

	public function getParams() : array
	{
		return $this->params;
	}

	public function getMethod() : string
	{
		return $this->method;
	}

	public function getTermUrl() : bool
	{
		return $this->termUrl;
	}
}