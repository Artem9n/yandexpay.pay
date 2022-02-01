<?php

namespace YandexPay\Pay\Utils;

use Bitrix\Main;
use YandexPay\Pay;

class ClassFinder
{
	private $namespace;
	private $namespaceLength;
	private $path;

	public static function forModule(): ClassFinder
	{
		return new static(
			Pay\Config::getNamespace(),
			Pay\Config::getModulePath()
		);
	}

	public function __construct($namespace, $path)
	{
		$this->namespace = ltrim($namespace, '\\');
		$this->path = $path;

		$this->namespaceLength = mb_strlen($this->namespace);
	}

	public function getPath($className): string
	{
		$relativeClassName =  $this->getRelativeName($className);
		$relativePath = str_replace('\\', Main\IO\Path::DIRECTORY_SEPARATOR, $relativeClassName);
		$relativePath = mb_strtolower($relativePath) . '.php';

		return
			$this->path
			. Main\IO\Path::DIRECTORY_SEPARATOR
			. $relativePath;
	}

	public function getRelativeName($className): string
	{
		if (mb_stripos($className, $this->namespace) !== 0)
		{
			throw new Main\NotSupportedException(sprintf(
				'class %s outside %s not supported',
				$className,
				$this->namespace
			));
		}

		return mb_substr($className, $this->namespaceLength + 1);
	}
}