<?php

namespace YandexPay\Pay\Component\Trading\Injection;

use Bitrix\Main;
use YandexPay\Pay;
use YandexPay\Pay\Reference\Assert;

class Form extends Pay\Component\Plain\Form
{
    protected $setup;

    public function prepareComponentParams(array $params) : array
    {


        /*$setup = $this->getSetup();
        $options = $setup->getOptions();

        $params['FIELDS'] = $options->getFields($setup->getEnvironment(), $setup->getSiteId());
        $params['TABS'] = $this->getSetup()->getOptions()->getTabs();*/


        return $params;
    }

    public function load($primary, array $select = [], bool $isCopy = false) : array
    {
        $result = $this->loadFieldsDefaults($select);
        return $result;
    }

    protected function loadFieldsDefaults(array $select = []) : array
    {
        $result = [];

        foreach ($this->getFields($select) as $fieldName => $field)
        {
            if (!isset($field['INJECTION'])) { continue; }

            Pay\Utils\BracketChain::set($result, $fieldName, $field['INJECTION']);
        }

        return $result;
    }

    public function add(array $values) : Main\Entity\Result
    {
        throw new Main\NotSupportedException();
    }

    public function update($primary, array $values) : Main\Entity\Result
    {
        $setup = $this->getSetup();
        return $setup->save();
    }


    protected function getSetup() : Pay\Trading\Injection\Model
    {

        return $this->setup;
    }

    /** @return Main\ORM\Data\DataManager  */
    protected function getDataClass() : string
    {
        return $this->getComponentParam('DATA_CLASS_NAME');
    }
}