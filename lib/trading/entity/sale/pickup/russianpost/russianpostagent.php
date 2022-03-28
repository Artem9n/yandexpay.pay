<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Pickup\RussianPost;

use Bitrix\Main;
use Bitrix\Main\ORM\EntityError;
use YandexPay\Pay\Config;
use YandexPay\Pay\Reference\Agent;

class RussianPostAgent extends Agent\Regular
{
	protected static $rootPath;
	protected static $urlPvz = 'https://otpravka-api.pochta.ru/1.0/unloading-passport/zip?type=ALL';
	protected static $urlPvzAll = 'https://otpravka-api.pochta.ru/1.0/delivery-point/findAll';
	protected static $pathUpload = '/ruspvz/';
	protected static $fileNameZip = 'pvz.zip';
	protected static $fileName;
	protected static $json = [];

	public static function getDefaultParams() : array
	{
		return [
			'interval' => 86400
		];
	}

	public static function run() : bool
	{
		if (!Main\Loader::includeModule('russianpost.post')) { return false; }

		Main\IO\Directory::createDirectory(static::getDir());

		static::unlinkFiles();
		static::downloadFilePvz();

		$zip = new \ZipArchive();

		if ($zip->open(static::getDir() . static::$fileNameZip) === true)
		{
			$zip->extractTo(static::getDir());
			$zip->close();
		}

		$baseDir = static::getDir();

		$directory = new \RecursiveDirectoryIterator($baseDir);
		$iterator = new \RecursiveIteratorIterator($directory);

		/** @var \DirectoryIterator $entry */
		foreach ($iterator as $entry)
		{
			if ($entry->isFile()
				&& $entry->getExtension() === 'txt')
			{
				static::$fileName = $entry->getFilename();
			}
		}

		ini_set('memory_limit', '2048M');

		if(!empty(static::$fileName))
		{
			$filePath = static::getDir() . static::$fileName;

			if(file_exists($filePath))
			{
				$content = file_get_contents($filePath);

				if(!empty($content))
				{
					$content = str_replace(['\\r', '\\n'], '', $content);

					static::$json = Main\Web\Json::decode($content);
				}
			}
		}




		/*$documentRoot = realpath(__DIR__ . '/../../../../../..');
		$upload = new RusPost\RusPostUploadPickup($documentRoot);
		$upload->downloadFilePvz();

		if(!$upload->checkHash())
		{
			$pickupPvz = $upload->getPickup();
			if(!empty($pickupPvz))
			{
				RusPost\RusPostPickup::setPickup($pickupPvz);
			}
		}

		return true;*/
	}

	protected static function downloadFilePvz() : void
	{
		$httpClient = new \Bitrix\Main\Web\HttpClient();
		$httpClient->setHeader('Accept', 'application/octet-stream');
		$httpClient->setHeader('Authorization', 'AccessToken ' . 'TuXcsZs4CPfa8R49Ox53Psf2r47xnDTG');
		$httpClient->setHeader('X-User-Authorization', 'Basic ' . 'a29yY2hlYm55QHRlY2gtZGlyZWN0b3IucnU6a29yY2hlYmFhMTIz');

		$httpClient->download(static::$urlPvz, static::getDir() . static::$fileNameZip);
		$httpClient->clearHeaders();
	}

	protected static function getDir() : string
	{
		return __DIR__ . static::$pathUpload;
	}

	protected static function unlinkFiles() : void
	{
		$directory = new \RecursiveDirectoryIterator(static::getDir());
		$iterator = new \RecursiveIteratorIterator($directory);
		$filenames = [];

		/** @var \DirectoryIterator $entry */
		foreach ($iterator as $entry)
		{
			if ($entry->isFile())
			{
				$filenames[] = $entry->getFilename();
			}
		}

		ksort($filenames);

		if (count($filenames) > 1) { return; }

		foreach ($filenames as $fileName)
		{
			if (file_exists(static::getDir() . '/' . $fileName))
			{
				unlink(static::getDir() . '/' . $fileName);
			}
		}
	}
}