<?php

namespace YandexPay\Pay\Reference\Event;

abstract class Regular extends Base
{
	/**
	 * @return array ������ ������������, �����:
	 *               module => string # �������� ������
	 *               event => string, # �������� �������
	 *               method => string, # �������� ������ (�������������)
	 *               sort => integer, # ���������� (�������������)
	 *               arguments => array # ��������� (�������������)
	 * */

	public static function getHandlers(): array
	{
		return [];
	}
}