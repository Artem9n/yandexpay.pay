<?php

namespace YandexPay\Pay\Result;

use YandexPay\Pay;

class Base
{
	protected $isErrorStrict = true;
	protected $isSuccess = true;
	/** @var Pay\Error\Base[] */
	protected $errors;
	/** @var Pay\Error\Base[] */
	protected $warnings;
	/** @var array|null */
	protected $data;

	public function __construct()
	{
		$this->errors = [];
		$this->warnings = [];
	}

	public function isSuccess()
	{
		return $this->isSuccess;
	}

	public function setErrorStrict($isStrict)
	{
		$this->isErrorStrict = (bool)$isStrict;
	}

	public function isErrorStrict()
	{
		return $this->isErrorStrict;
	}

	public function invalidate()
	{
		if ($this->isErrorStrict)
		{
			$this->isSuccess = false;
		}
	}

	public function addError(Pay\Error\Base $error)
	{
		if ($this->isErrorStrict)
		{
			$this->isSuccess = false;
			$this->errors[] = $error;
		}
		else
		{
			$this->addWarning($error);
		}
	}

	public function addErrors(array $errors)
	{
		if ($this->isErrorStrict)
		{
			$this->isSuccess = false;

			foreach ($errors as $error)
			{
				$this->errors[] = $error;
			}
		}
		else
		{
			$this->addWarnings($errors);
		}
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function getErrorMessages()
	{
		$result = [];

		foreach ($this->errors as $error)
		{
			$result[] = $error->getMessage();
		}

		return $result;
	}

	/**
	 * @return bool
	 */
	public function hasErrors()
	{
		return !empty($this->errors);
	}

	public function addWarning(Pay\Error\Base $warning)
	{
		$this->warnings[] = $warning;
	}

	public function addWarnings(array $warnings)
	{
		foreach ($warnings as $warning)
		{
			$this->warnings[] = $warning;
		}
	}

	/**
	 * @return Pay\Error\Base[]
	 */
	public function getWarnings()
	{
		return $this->warnings;
	}

	/**
	 * @return string[]
	 */
	public function getWarningMessages()
	{
		$result = [];

		foreach ($this->warnings as $warning)
		{
			$result[] = $warning->getMessage();
		}

		return $result;
	}

	/**
	 * @return bool
	 */
	public function hasWarnings()
	{
		return !empty($this->warnings);
	}

	/**
	 * Sets data of the result.
	 * @param array $data
	 */
	public function setData(array $data)
	{
		$this->data = $data;
	}

	/**
	 * @return array|null
	 */
	public function getData()
	{
		return $this->data;
	}
}