<?php

namespace YandexPay\Pay\Exceptions;

use Bitrix\Main;

class Secure3dRedirect extends Main\SystemException
{
	private $url;
	private $data;
	private $key;

	public function __construct(string $url, $data, string $key)
	{
		parent::__construct('3ds redirect');
		$this->url = $url;
		$this->data = $data;
		$this->key = $key;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function getData()
	{
		return $this->data;
	}

	public function getKey()
	{
		return $this->key;
	}
}