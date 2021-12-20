<?php

namespace YandexPay\Pay\Ui\UserField\Helper;

class SummaryTemplate
{
	public static function render(string $template, array $vars) : string
	{
		$usedKeys = static::getUsedKeys($template);
		[$replaces, $removes] = static::splitValidVariables($vars, $usedKeys);

		$result = static::applyRemoveVariables($template, $removes);
		$result = static::applyReplaceVariables($result, $replaces);

		return $result;
	}

	protected static function applyRemoveVariables(string $template, array $keys) : string
	{
		$result = $template;

		foreach ($keys as $key)
		{
			$result = static::removeVariable($result, $key);
		}

		return $result;
	}

	protected static function applyReplaceVariables(string $template, array $replaces) : string
	{
		$result = $template;

		foreach ($replaces as $key => $value)
		{
			$result = static::replaceVariable($result, $key, $value);
		}

		return $result;
	}

	public static function getUsedKeys(string $template) : array
	{
		if (preg_match_all('/#([A-Z0-9_.]+?)#/', $template, $matches))
		{
			$result = $matches[1];
		}
		else
		{
			$result = [];
		}

		return $result;
	}

	public static function normalizeNames(array $fields) : array
	{
		$result = [];

		foreach ($fields as $name => $field)
		{
			$normalized = static::normalizeFieldName($name);

			$result[$normalized] = $field;
		}

		return $result;
	}

	protected static function normalizeFieldName(string $name) : string
	{
		if (mb_strpos($name, '[') === false) { return $name; }

		$parts = [];

		foreach (explode('[', $name) as $part)
		{
			$part = rtrim($part, ']');

			if ($part === '') { continue; }

			$parts[] = $part;
		}

		return implode('.', $parts);
	}

	protected static function splitValidVariables(array $vars, array $keys) : array
	{
		$replaces = [];
		$invalid = [];

		foreach ($keys as $key)
		{
			if (isset($vars[$key]) && static::isValidVariable($vars[$key]))
			{
				$replaces[$key] = $vars[$key];
			}
			else
			{
				$invalid[] = $key;
			}
		}

		return [ $replaces, $invalid ];
	}

	protected static function isValidVariable($value) : bool
	{
		return (isset($value) && is_scalar($value) && (string)$value !== '');
	}

	protected static function removeVariable(string $template, string $key) : string
	{
		$search = '#' . $key . '#';
		$result = $template;

		while (($searchPosition = mb_strpos($result, $search)) !== false)
		{
			$before = mb_substr($result, 0, $searchPosition);
			$before = static::trimRightPart($before);
			$after = mb_substr($result, $searchPosition + strlen($search));
			$after = static::trimLeftPart($after);

			if (isset($after[0]) && $after[0] === ',' && mb_substr($before, -1) === '.')
			{
				$after = mb_substr($after, 1);
			}

			if (isset($after[0]) && $after[0] === '(')
			{
				$after = ' ' . $after;
			}

			$result = $before . $after;
		}

		return $result;
	}

	protected static function trimRightPart(string $part) : string
	{
		return preg_replace('/[,(]?[^#.,()]*$/', '', $part);
	}

	protected static function trimLeftPart(string $part) : string
	{
		return preg_replace('/^[^#.,(]+/', '', $part);
	}

	protected static function replaceVariable(string $template, string $key, $value) : string
	{
		return str_replace('#' . $key . '#' , $value, $template);
	}
}