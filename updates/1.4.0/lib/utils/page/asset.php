<?php

namespace YandexPay\Pay\Utils\Page;

use Bitrix\Main;

class Asset
{
	public static function getAssetAbsolutePath(string $assetPath) : ?string
	{
		$paths = [$assetPath];
		if (Main\Page\Asset::canUseMinifiedAssets() && preg_match('/(.+)\.(js|css)$/i', $assetPath, $matches))
		{
			array_unshift($paths, $matches[1].".min.".$matches[2]);
		}

		$result = null;
		$maxMtime = 0;
		foreach ($paths as $path)
		{
			$filePath = $_SERVER['DOCUMENT_ROOT'] . $path;
			if (file_exists($filePath) && ($mtime = filemtime($filePath)) > $maxMtime && filesize($filePath) > 0)
			{
				$maxMtime = $mtime;
				$result = $filePath;
			}
		}

		return $result;
	}
}