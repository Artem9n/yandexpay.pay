<?php

namespace YandexPay\Pay\Exceptions;

use Bitrix\Main;

class Secure3dRedirect extends Main\SystemException
{
	private $url;
	private $params;
	private $method;
	private $termUrl;
	private $view;

	public function __construct(string $url, $params, bool $termUrl = false, string $method = 'POST', string $view = 'form')
	{
		parent::__construct('3ds redirect');

		$this->url = $url;
		$this->params = $params;
		$this->method = $method;
		$this->termUrl = $termUrl;
		$this->view = $view;
	}

	public function getUrl() : string
	{
		return $this->url;
	}

	/**
	 *
	 * @return string|array
	 */
	public function getParams()
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

	public function getView() : string
	{
		return $this->view;
	}
}