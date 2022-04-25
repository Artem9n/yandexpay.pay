<?php
namespace YandexPay\Pay\Trading\Action\Rest\Authorize;

use Bitrix\Main;
use YandexPay\Pay\Trading\Action\Rest;

class Action extends Rest\Reference\EffectiveAction
{
	public function bootstrap() : void
	{
		$this->bootJson();
	}

	public function process() : Main\HttpResponse
	{
		$request = $this->convertHttpToRequest(Request::class);
		$response = $this->makeResponse();
		$state = $this->makeState(Rest\State\Payment::class);

		(new Rest\Pipeline())
			->pipe(new Rest\Stage\OrderLoad($request->getOrderId(), $request->getHash()))
			->pipe(new Rest\Authorize\Stage\UserAuthorize())
			->pipe(new Rest\Authorize\Stage\RedirectCollector($response, $request->getSuccessUrl(), 'redirect'))
			->process($state);

		return $this->convertResponseToHttp($response);
	}
}