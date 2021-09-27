<?php
namespace YandexPay\Pay\Ui\Reference;

abstract class Form extends Page
{
	public function hasRequest() : bool
	{
		return $this->request->isPost();
	}

	abstract public function processRequest();

	protected function showFormProlog() : void
	{
		$postUrl = $this->getFormActionUri();

		echo '<form method="post" action="' . htmlspecialcharsbx($postUrl) . '" enctype="multipart/form-data">';
		echo bitrix_sessid_post();
	}

	protected function getFormActionUri() : string
	{
		return $this->request->getRequestUri();
	}

	protected function showFormEpilog() : void
	{
		echo '</form>';
	}
}