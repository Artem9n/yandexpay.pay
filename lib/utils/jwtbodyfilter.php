<?php
namespace YandexPay\Pay\Utils;

use Bitrix\Main;
use Firebase\JWT;
use YandexPay\Pay\Config;

class JwtBodyFilter implements Main\Type\IRequestFilter
{
	protected $jwkEndpoint;

	public function __construct(string $jwkEndpoint)
	{
		$this->jwkEndpoint = $jwkEndpoint;
	}

	public function filter(array $values) : array
	{
		$raw = $this->input();
		$keys = $this->keys();

		return [
			'post' => $this->decode($raw, $keys),
		];
	}

	protected function input() : string
	{
		return file_get_contents('php://input');
	}

	protected function keys() : array
	{
		$cache = Main\Data\Cache::createInstance();
		$cacheTtl = 86400; // one day
		$cacheId = 'jwk:' . md5($this->jwkEndpoint);

		if ($cache->initCache($cacheTtl, $cacheId, Config::getModuleName()))
		{
			$data = $cache->getVars();
		}
		else
		{
			$data = $this->keysFetch();

			$cache->startDataCache();
			$cache->endDataCache($data);
		}

		return $this->keysParse($data);
	}

	protected function keysFetch() : array
	{
		$client = new Main\Web\HttpClient();
		$raw = $client->get($this->jwkEndpoint);
		$errors = $client->getError();

		if (!empty($errors))
		{
			$errorMessage = reset($errors);
			$errorKey = key($errors);

			throw new Main\SystemException(sprintf('[%s] %s', $errorKey, $errorMessage));
		}

		$data = Main\Web\Json::decode($raw);

		if (!is_array($data))
		{
			throw new Main\SystemException('cant parse jwk endpoint response');
		}

		return $data;
	}

	protected function keysParse(array $data) : array
	{
		try
		{
			return JWT\JWK::parseKeySet($data);
		}
		catch (\Exception $exception)
		{
			throw new Main\SystemException(
				$exception->getMessage(),
				$exception->getCode(),
				$exception->getFile(),
				$exception->getLine(),
				$exception
			);
		}
	}

	protected function decode(string $raw, array $keys) : array
	{
		try
		{
			$data = JWT\JWT::decode($raw, $keys);

			return (array)$data;
		}
		catch (\Exception $exception)
		{
			throw new Main\SystemException(
				$exception->getMessage(),
				$exception->getCode(),
				$exception->getFile(),
				$exception->getLine(),
				$exception
			);
		}
	}
}