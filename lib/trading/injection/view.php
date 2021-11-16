<?php

namespace YandexPay\Pay\Trading\Injection;

use YandexPay\Pay\Reference\Storage;
use YandexPay\Pay\Reference\Concerns;

class View extends Storage\View
{
    use Concerns\HasMessage;

    public function getFields() : array
    {
        $environment = $this->getEnvironment();
        /*print_r($this->getTableFields());
        die;*/
        return $this->getTableFields([
            'SETUP_ID' => [
                'TYPE' => 'enumeration',
                'VALUES' => $environment->getPaySystem(), // todo
            ],
            'BEHAVIOR' => [
                'TYPE' => 'LIST',
                'VALUES' => "", // todo
            ],
            'SELECTOR' => [
                'TYPE' => 'STRING',
                'VALUES' => "", // todo
            ],

        ]);
    }
}