<?php

$file = fopen(__DIR__ . '/city.csv', 'rb');
$rows = [];
$headers = fgetcsv($file);

while ($row = fgetcsv($file))
{
	$rows[] = array_combine($headers, \Bitrix\Main\Text\Encoding::convertEncoding($row, 'UTF-8', LANG_CHARSET));
}

fclose($file);

return $rows;