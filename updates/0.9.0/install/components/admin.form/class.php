<?php

namespace YandexPay\Pay\Components;

use Bitrix\Main;
use YandexPay\Pay;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) { die(); }

class AdminForm extends \CBitrixComponent
{
    protected static $langPrefix = 'YANDEX_PAY_FORM_EDIT_';

    /** @var Pay\Component\Reference\Form */
    protected $provider;

    public function onPrepareComponentParams($arParams) : array
    {
        $arParams['FORM_ID'] = trim($arParams['FORM_ID']);
        $arParams['TITLE'] = trim($arParams['TITLE']);
        $arParams['TITLE_ADD'] = trim($arParams['TITLE_ADD']);
        $arParams['BTN_SAVE'] = trim($arParams['BTN_SAVE']);
        $arParams['BTN_APPLY'] = trim($arParams['BTN_APPLY']);
        $arParams['LIST_URL'] = trim($arParams['LIST_URL']);
        $arParams['SAVE_URL'] = trim($arParams['SAVE_URL']);
        $arParams['CONTEXT_MENU'] = (array)$arParams['CONTEXT_MENU'];
        $arParams['TABS'] = (array)($arParams['TABS'] ?? []);
        $arParams['FORM_BEHAVIOR'] = ($arParams['FORM_BEHAVIOR'] === 'steps' ? 'steps' : 'tabs');
        $arParams['COPY'] = (bool)$arParams['COPY'];
	    $arParams['ALLOW_SAVE'] = !isset($arParams['ALLOW_SAVE']) || $arParams['ALLOW_SAVE'];
	    $arParams['SAVE_PARTIALLY'] = isset($arParams['SAVE_PARTIALLY']) && $arParams['SAVE_PARTIALLY'];
	    $arParams['PROVIDER_CLASS_NAME'] = trim($arParams['PROVIDER_CLASS_NAME']);

        if (empty($arParams['TABS']))
        {
            $arParams['TABS'] = [
            	[ 'name' => $this->getLang('DEFAULT_TAB_NAME') ]
            ];
        }

        return $arParams;
    }

    public function executeComponent()
    {
        try
        {
            if ($this->hasCancelRequest())
            {
                $this->redirectCancel();
            }

	        $this->setTitle();
			$this->loadModules();

			$this->prepareParams();
	        $this->initResult();

			$this->checkParams();

	        $this->loadItem();
	        $this->buildContextMenu();
	        $this->buildTabs();
	        $this->buildButtons();

	        $isStepsBehavior = ($this->arParams['FORM_BEHAVIOR'] === 'steps');
	        $requestStep = $this->getRequestStep();
	        $hasRequest = $this->hasRequest();
	        $hasSaveRequest = $this->hasSaveRequest();
	        $isFoundRequestStep = false;
	        $isFirstTab = true;

            foreach ($this->arResult['TABS'] as &$tab)
            {
            	$tabFields = !empty($tab['SELECT']) || $isFirstTab ? $this->loadFields($tab['SELECT']) : [];
            	$stepValidateResult = true;

            	$this->registerTabFields($tab, $tabFields);

            	if ($hasRequest)
	            {
	                $this->fillRequest($tabFields);
	                $this->resolveDependency($tabFields);

	                if (
	                    $isStepsBehavior
	                    && (
	                        $hasSaveRequest // validate all on save
	                        || (!$isFoundRequestStep && $requestStep !== $tab['STEP']) // validate previous steps on move
                        )
                    )
	                {
		                $stepValidateResult = $this->validateRequest($tabFields);
			        }
		        }
            	else
	            {
	            	$this->resolveDependency($tabFields);
	            }

            	$this->registerFields($tabFields);

		        if ($isStepsBehavior && !$isFoundRequestStep)
	            {
	            	$this->arResult['STEP'] = $tab['STEP'];
	            	$this->arResult['STEP_FINAL'] = $tab['FINAL'];

	                if (!$stepValidateResult || $requestStep === $tab['STEP'])
	                {
	                    $isFoundRequestStep = true;
	                }
		        }

	            $isFirstTab = false;
            }
            unset($tab);

            if (!$isStepsBehavior && $hasSaveRequest)
            {
                $this->validateRequest();
            }

			if ($this->hasAjaxAction())
			{
				$this->processAjaxAction();
			}
			else if ($this->hasPostAction())
			{
				if (!check_bitrix_sessid())
				{
					$this->addError($this->getLang('EXPIRE_SESSION'));
				}

				if (!$this->hasErrors())
				{
					$this->processPostAction();
				}

				if (!$this->hasErrors())
				{
					$this->afterSave($this->getPostAction());
				}
			}
			else if ($hasSaveRequest)
            {
	            $savePrimary = null;

	            if (!$this->arParams['ALLOW_SAVE'])
	            {
		            $this->addError($this->getLang('SAVE_DISALLOW'));
	            }
            	else if (!check_bitrix_sessid())
	            {
	                $this->addError($this->getLang('EXPIRE_SESSION'));
	            }
	            else if (!$this->hasErrors())
	            {
	                $savePrimary = $this->saveFull();
	            }
	            else if ($this->arParams['SAVE_PARTIALLY'])
	            {
	            	$savePrimary = $this->savePartially();
                }

	            if ($savePrimary !== null && !$this->hasErrors())
	            {
		            $this->afterSave('save', $savePrimary);
	            }
            }

            $this->extendItem();

	        $this->includeComponentTemplate();
        }
        catch (Main\SystemException $exception)
        {
            $this->addError($exception->getMessage());

	        $this->includeComponentTemplate('exception');
        }
    }

    protected function initResult() : void
    {
        $this->arResult['SUCCESS'] = false;
        $this->arResult['ACTION'] = null;
        $this->arResult['STEP'] = null;
        $this->arResult['STEP_FINAL'] = false;
        $this->arResult['FIELDS'] = [];
        $this->arResult['ITEM'] = [];
        $this->arResult['ITEM_ORIGINAL'] = [];
        $this->arResult['ERRORS'] = [];
        $this->arResult['FIELD_ERRORS'] = [];
        $this->arResult['TABS'] = [];
        $this->arResult['BUTTONS'] = [];
        $this->arResult['HAS_REQUEST'] = false;
    }

    protected function getRequiredParams() : array
    {
        $provider = $this->getProvider();

        return [ 'FORM_ID' ] + $provider->getRequiredParams();
    }

    protected function checkParams() : void
    {
        foreach ($this->getRequiredParams() as $paramKey)
        {
            if (!empty($this->arParams[ $paramKey ])) { continue; }

            throw new Main\SystemException($this->getLang('PARAM_REQUIRE', [
                '#PARAM#' => $paramKey
            ]));
        }
    }

    protected function getRequiredModules() : array
    {
        return array_merge(
			$this->getProvider()->getRequiredModules(),
	        [ 'yandexpay.pay' ]
        );
    }

    protected function loadModules() : void
    {
        foreach ($this->getRequiredModules() as $module)
        {
	        $this->loadModule($module);
        }
    }

    protected function loadModule($module) : void
    {
	    if (!Main\Loader::includeModule($module))
	    {
		    $message = $this->getLang('MODULE_REQUIRE', [
			    '#MODULE#' => $module
		    ]);

		    throw new Main\SystemException($message);
	    }
    }

	protected function prepareParams() : void
	{
		$this->arParams = $this->getProvider()->prepareComponentParams($this->arParams);
	}

    protected function addFieldError($fieldName, $message) : void
    {
        $this->arResult['FIELD_ERRORS'][$fieldName] = true;

        $this->addError($message);
    }

    protected function addError($message) : void
    {
        $this->arResult['ERRORS'][] = $message;
    }

    public function hasErrors() : bool
    {
        return !empty($this->arResult['ERRORS']);
    }

    public function showErrors() : void
    {
        \CAdminMessage::ShowMessage([
            'TYPE' => 'ERROR',
            'MESSAGE' => implode('<br />', $this->arResult['ERRORS']),
            'HTML' => true
        ]);
    }

    protected function setTitle() : void
    {
        global $APPLICATION;

        $title = $this->arParams['TITLE'];
        $primary = $this->getPrimary();

        if ($primary === null && $this->arParams['TITLE_ADD'] !== '')
        {
            $title = $this->arParams['TITLE_ADD'];
        }

        if ($title !== '')
        {
            $APPLICATION->SetTitle($title);
        }
    }

    protected function getFieldsSelect() : array
    {
        $result = [];

        foreach ($this->arParams['TABS'] as $tab)
        {
            if (!empty($tab['fields']))
            {
                foreach ($tab['fields'] as $field)
                {
                    $result[] = $field;
                }
            }
        }

        return $result;
    }

    protected function hasAjaxAction() : bool
    {
        return ($this->getAjaxAction() !== null);
    }

    protected function getAjaxAction() : ?string
    {
        return $this->request->getPost('ajaxAction');
    }

    protected function processAjaxAction() : void
    {
        global $APPLICATION;

        $ajaxAction = $this->getAjaxAction();
        $provider = $this->getProvider();

        try
        {
	        $data = $this->arResult['ITEM'];
	        $data['PRIMARY'] = $this->getPrimary();

            $response = $provider->processAjaxAction($ajaxAction, $data);
        }
        catch (Main\SystemException $exception)
        {
            $response = [
                'status' => 'error',
                'message' => $exception->getMessage()
            ];
        }

        $APPLICATION->RestartBuffer();
        echo Main\Web\Json::encode($response);
        die();
    }

	protected function hasPostAction() : bool
	{
		return ($this->getPostAction() !== null);
	}

	protected function getPostAction() : ?string
	{
		return $this->request->get('postAction');
	}

	protected function processPostAction() : void
	{
		$postAction = $this->getPostAction();
		$provider = $this->getProvider();

		try
		{
			$data = $this->arResult['ITEM'];
			$data['PRIMARY'] = $this->getPrimary();

			$provider->processPostAction($postAction, $data);
		}
		catch (Main\SystemException $exception)
		{
			$this->addError($exception->getMessage());
		}
	}

    protected function hasRequest() : bool
    {
	    $this->arResult['HAS_REQUEST'] = $this->hasStepRequest() || $this->hasSaveRequest() || $this->hasPostAction() || $this->hasAjaxAction();

        return $this->arResult['HAS_REQUEST'];
    }

    protected function hasCancelRequest() : bool
    {
        return ($this->request->getPost('cancel') !== null);
    }

    protected function hasStepRequest() : bool
    {
        return ($this->request->getPost('stepAction') !== null);
    }

    protected function hasSaveRequest() : bool
    {
        return ($this->request->getPost('apply') !== null || $this->request->getPost('save') !== null);
    }

    protected function isAjaxForm() : bool
    {
    	return $this->request->getPost('ajaxForm') === 'Y';
    }

    protected function getRequestStep() : int
    {
        $stepCount = \count($this->arResult['TABS']);
        $stepIndex = (int)$this->request->getPost('STEP');

        // step action

        $stepAction = $this->request->getPost('stepAction');

        switch (true)
        {
	        case ($stepAction === 'previous'):
	            --$stepIndex;
	        break;

	        case ($stepAction === 'next'):
	            ++$stepIndex;
	        break;

	        case (is_numeric($stepAction)):
                $stepIndex = (int)$stepAction;
	        break;
        }

        // normalize index

        if ($stepIndex <= 0)
        {
            $stepIndex = 0;
        }
        else if ($stepIndex >= $stepCount)
        {
            $stepIndex = $stepCount - 1;
        }

        return $stepIndex;
    }

    protected function fillRequest($fields) : void
    {
		$provider = $this->getProvider();

        foreach ($fields as $field)
        {
            if ($field['USER_TYPE']['BASE_TYPE'] === 'file')
            {
	            $this->getFileByRequestKey($_POST, $_FILES, $field['FIELD_NAME'], $this->arResult['ITEM']);
            }
            else
            {
	            $this->getValueByRequestKey($_POST, $field['FIELD_NAME'], $this->arResult['ITEM']);
            }
        }

        $this->arResult['ITEM'] = $provider->modifyRequest($this->arResult['ITEM'], $fields);
    }

    protected function getValueByRequestKey($values, $key, &$result) : void
    {
        $keyChain = $this->splitFieldNameToChain($key);
        $value = $this->getValueByChain($values, $keyChain);

        $this->setValueByChain($result, $keyChain, $value);
    }

    protected function getFileByRequestKey($post, $files, $key, &$result) : void
    {
	    $keyChain = $this->splitFieldNameToChain($key);

	    if (count($keyChain) > 1) { throw new Main\NotImplementedException(); }

        $requestKey = reset($keyChain);
        $deleteRequestKey = $requestKey . '_del';
        $oldIdRequestKey = $requestKey . '_old_id';

        $request = isset($files[$requestKey]) && is_array($files[$requestKey]) ? $files[$requestKey] : [];

        if (isset($post[$deleteRequestKey]))
	    {
		    $request['del'] = ($post[$deleteRequestKey] === 'Y');
	    }

        if (isset($post[$oldIdRequestKey]))
	    {
		    $request['old_id'] = (int)$post[$oldIdRequestKey];
	    }

        $result[$requestKey] = $request;
    }

	protected function resolveDependency(&$fields) : void
	{
		$statuses = $this->getDependencyStatuses($fields);

		foreach ($statuses as $fieldName => $status)
		{
			if (!isset($fields[$fieldName])) { continue; }

			$fields[$fieldName]['DEPEND_HIDDEN'] = $status;
		}
	}

	protected function getDependencyStatuses($fields) : array
	{
		$result = [];

		foreach ($fields as $fieldName => $field)
		{
			if (!isset($field['DEPEND'])) { continue; }

			$result[$fieldName] = !Pay\Utils\Userfield\DependField::test($field['DEPEND'], $this->arResult['ITEM']);
		}

		return $result;
	}

    protected function validateRequest($fields = null) : bool
    {
    	if ($fields === null)
	    {
	    	$fields = $this->arResult['FIELDS'];
	    }

    	$data = $this->arResult['ITEM'];
    	$data['PRIMARY'] = $this->getPrimary();

        $provider = $this->getProvider();
        $validationResult = $provider->validate($data, $fields);
        $result = false;

        if ($validationResult->isSuccess())
        {
            $result = true;
        }
        else
        {
            $errors = $validationResult->getErrors();

            if (!empty($errors))
            {
                foreach ($errors as $error)
                {
                    $errorCustomData = method_exists($error, 'getCustomData') ? $error->getCustomData() : null;

                    if (isset($errorCustomData['FIELD']))
                    {
                    	$this->addFieldError($errorCustomData['FIELD'], $error->getMessage());
                    }
                    else
                    {
                        $this->addError($error->getMessage());
                    }
                }
            }
            else
            {
                $this->addError($this->getLang('VALIDATE_ERROR_UNDEFINED'));
            }
        }

        return $result;
    }

    protected function saveFull() : ?int
    {
	    $fields = $this->arResult['ITEM'];

    	return $this->save($fields);
    }

    protected function savePartially() : ?int
    {
	    $fields = $this->arResult['ITEM'];
	    $fieldsOriginal = $this->arResult['ITEM_ORIGINAL'];

	    foreach ($this->getFieldsWithError() as $fieldName)
	    {
			if (!array_key_exists($fieldName, $fields)) { continue; }

			if (array_key_exists($fieldName, $fieldsOriginal))
			{
				$fields[$fieldName] = $fieldsOriginal[$fieldName];
			}
			else
			{
				unset($fields[$fieldName]);
			}
	    }

	    if (!empty($fields))
	    {
	    	$result = $this->save($fields);
	    }
	    else
	    {
	    	$result = null;
	    }

    	return $result;
    }

    protected function save($fields) : ?int
    {
        $provider = $this->getProvider();
        $primary = $this->getPrimary();
        $result = null;

        if ($primary !== null)
        {
            $saveResult = $provider->update($primary, $fields);
        }
        else
        {
            $saveResult = $provider->add($fields);

            if ($saveResult->isSuccess())
            {
                $primary = $saveResult->getId();
            }
        }

        if ($saveResult->isSuccess())
        {
			$result = $primary;
        }
        else
        {
            $errors = $saveResult->getErrors();

            if (!empty($errors))
            {
                foreach ($errors as $error)
                {
                    $this->addError($error->getMessage());
                }
            }
            else
            {
                $this->addError($this->getLang('SAVE_ERROR_UNDEFINED'));
            }
        }

        return $result;
    }

    protected function afterSave($action, $primary = null) : void
    {
    	if ($this->isAjaxForm())
	    {
			$this->arResult['PRIMARY'] = $primary ?? $this->getPrimary();
			$this->arResult['ACTION'] = $action;
			$this->arResult['SUCCESS'] = true;
	    }
    	else
	    {
	    	$this->redirectAfterSave($primary);
	    }
    }

    protected function getPrimary($useOrigin = false) : ?int
    {
	    $result = null;

	    if (
	    	!empty($this->arParams['PRIMARY'])
	        && (!$this->arParams['COPY'] || $useOrigin)
	    )
	    {
		    $result = $this->arParams['PRIMARY'];
	    }

    	return $result;
    }

    protected function redirectCancel() : void
    {
        LocalRedirect($this->arParams['LIST_URL']);
        die();
    }

    protected function redirectAfterSave($primary = null) : void
    {
        global $APPLICATION;

        $redirectUrl = (string)($this->arParams['REDIRECT_URL'] ?: '');
	    $parameters = [];

	    if ($primary !== null)
	    {
		    $parameters['id'] = $primary;
	    }

	    if ($this->arParams['FORM_BEHAVIOR'] !== 'steps')
	    {
		    $activeTabRequestKey = $this->arParams['FORM_ID'] . '_active_tab';
		    $activeTab = $this->request->getPost($activeTabRequestKey);

		    $parameters[$activeTabRequestKey] = $activeTab;
	    }

	    if ($this->request->getPost('save'))
	    {
		    $redirectUrl = (string)($this->arParams['SAVE_URL'] ?: $this->arParams['LIST_URL']);
	    }

        if ($redirectUrl !== '')
        {
        	$leftParameters = [];

        	foreach ($parameters as $name => $value)
	        {
	        	$searchHolder = '#' . mb_strtoupper($name) . '#';
	        	$searchPosition = mb_strpos($redirectUrl, $searchHolder);

	        	if ($searchPosition !== false)
		        {
		        	$redirectUrl = str_replace($searchHolder, $value, $redirectUrl);
		        }
	        	else
		        {
		        	$leftParameters[$name] = $value;
		        }
	        }

        	if (!empty($leftParameters))
	        {
	        	$redirectUrl .=
			        (mb_strpos($redirectUrl, '?') === false ? '?' : '&')
			        . http_build_query($leftParameters);
	        }
        }
        else
        {
	        $redirectUrl = $APPLICATION->GetCurPageParam(
	        	http_build_query($parameters),
		        array_keys($parameters)
	        );
        }

        LocalRedirect($redirectUrl);
        die();
    }

    protected function loadItem() : void
    {
        $primary = $this->getPrimary(true);

        if ($primary !== null)
        {
	        $provider = $this->getProvider();
            $fieldsSelect = $this->getFieldsSelect();

            $this->arResult['ITEM'] = $provider->load($primary, $fieldsSelect, $this->arParams['COPY']);
            $this->arResult['ITEM_ORIGINAL'] = $this->arResult['ITEM'];
        }
    }

    protected function loadFields(array $select) : array
    {
	    $fields = $this->getProvider()->getFields($select, $this->arResult['ITEM']);

	    $fields = $this->extendFields($fields);

	    return $this->sortFields($fields);
    }

	protected function extendFields(array $fields) : array
	{
		foreach ($fields as $name => &$field)
		{
			$field = Pay\Ui\Userfield\Helper\Field::extend($field, $name);
		}
		unset($field);

		return $fields;
	}

	protected function sortFields(array $fields) : array
	{
		$fieldsWithSort = array_filter($fields, static function(array $field) { return isset($tab['SORT']); });

		if (count($fieldsWithSort) === 0) { return $fields; }

		uasort($fields, static function(array $fieldA, array $fieldB) {
			$sortA = $fieldA['SORT'] ?? 5000;
			$sortB = $fieldB['SORT'] ?? 5000;

			if ($sortA === $sortB) { return 0; }

			return $sortA < $sortB ? -1 : 1;
		});

		return $fields;
	}

    protected function registerFields($fields) : void
    {
    	$this->arResult['FIELDS'] += $fields;
    }

    protected function extendItem() : void
    {
        $provider = $this->getProvider();
        $isStepsBehavior = ($this->arParams['FORM_BEHAVIOR'] === 'steps');
        $selectFields = [];

        foreach ($this->arResult['TABS'] as $tab)
        {
            if (!$isStepsBehavior)
            {
                array_splice($selectFields, -1, 0, $tab['FIELDS']);
            }
            else if ($tab['STEP'] === $this->arResult['STEP'])
            {
                $selectFields = $tab['FIELDS'];
            }
        }

        $this->arResult['ITEM'] = $provider->extend($this->arResult['ITEM'], $selectFields);
    }

    protected function buildContextMenu() : void
    {
		$this->arResult['CONTEXT_MENU'] = $this->arParams['CONTEXT_MENU']; // simple copy, need for future modifications
    }

    protected function buildTabs() : void
    {
        $paramTabs = $this->arParams['TABS'];
        $countTabs = count($paramTabs);
        $hasFinalTab = false;
        $tabIndex = 0;
        $result = [];

        foreach ($paramTabs as $paramTab)
        {
            $isFinalTab = (!empty($paramTab['final']) || (!$hasFinalTab && $tabIndex === $countTabs - 1));

            if ($isFinalTab)
            {
                $hasFinalTab = true;
            }

            $result[] = [
                'STEP' => $tabIndex,
                'FINAL' => $isFinalTab,
                'DIV' => 'tab' . $tabIndex,
                'TAB' => $paramTab['name'],
                'LAYOUT' => $paramTab['layout'] ?: 'default',
                'SELECT' => $paramTab['fields'] ?: [],
                'FIELDS' => [],
                'HIDDEN' => [],
                'DATA' => isset($paramTab['data']) ? (array)$paramTab['data'] : []
            ];

            $tabIndex++;
        }

        $this->arResult['TABS'] = $result;
    }

    protected function buildButtons() : void
    {
    	if (!empty($this->arParams['BUTTONS']))
	    {
	        $this->arResult['BUTTONS'] = (array)$this->arParams['BUTTONS'];
	    }
		else if ($this->arParams['FORM_BEHAVIOR'] === 'steps')
		{
			$this->arResult['BUTTONS'] = [
				[ 'BEHAVIOR' => 'previous' ],
				[ 'BEHAVIOR' => 'next' ],
			];
		}
		else
		{
			$this->arResult['BUTTONS'] = [
				[ 'BEHAVIOR' => 'save' ],
				[ 'BEHAVIOR' => 'apply' ],
			];
		}
    }

    protected function registerTabFields(&$tab, $fields) : void
    {
        foreach ($fields as $fieldKey => $field)
        {
	        if (!empty($field['HIDDEN']) && $field['HIDDEN'] !== 'N')
	        {
	            $tab['HIDDEN'][] = $fieldKey;
	        }
	        else
	        {
	            $tab['FIELDS'][] = $fieldKey;
	        }
        }
    }

	public function getField($fieldKey) : ?array
	{
        return $this->arResult['FIELDS'][$fieldKey] ?? null;
	}

    public function hasFieldError($field) : bool
    {
        return !empty($this->arResult['FIELD_ERRORS'][$field['FIELD_NAME']]);
    }

    public function getFieldsWithError() : array
    {
    	return array_keys($this->arResult['FIELD_ERRORS']);
    }

    public function getFieldTitle($field) : ?string
    {
        return $this->getFirstNotEmpty(
            $field,
            [ 'EDIT_FORM_LABEL', 'LIST_COLUMN_LABEL', 'LIST_FILTER_LABEL' ]
        );
    }

    public function getFieldValue($field)
    {
        // try fetch from item

        $keyChain = $this->splitFieldNameToChain($field['FIELD_NAME']);
        $result = $this->getValueByChain($this->arResult['ITEM'], $keyChain);

		// may be defined value

	    if ($result !== null) { return $result; }

		if (isset($field['VALUE']))
        {
            $result = $field['VALUE'];
        }
        else if (isset($field['SETTINGS']['DEFAULT_VALUE']))
        {
			$result = $field['SETTINGS']['DEFAULT_VALUE'];
        }

        return $result;
    }

    public function normalizeFieldValue($field, $value)
    {
    	if ($field['MULTIPLE'] !== 'N' && is_scalar($value) && (string)$value !== '')
	    {
			$result = [ $value ];
	    }
    	else
	    {
	    	$result = $value;
	    }

    	return $result;
    }

    public function getOriginalValue($field)
    {
	    $keyChain = $this->splitFieldNameToChain($field['FIELD_NAME']);

	    return $this->getValueByChain($this->arResult['ITEM_ORIGINAL'], $keyChain);
    }

    public function getFieldHtml($field, $value = null) : ?string
    {
        $control = $this->getFieldControl($field, $value);

		return $control['CONTROL'] ?? null;
    }

	public function getFieldControl($field, $value = null) : ?array
	{
		if (!empty($field['HIDDEN']) && $field['HIDDEN'] !== 'N') { return null; }

		$value = $value ?? $this->getFieldValue($field);
		$value = $this->normalizeFieldValue($field, $value);

		return Pay\Ui\Userfield\Helper\Renderer::getEditRow($field, $value, $this->arResult['ITEM']);
	}

    protected function getFirstNotEmpty($data, $keys)
    {
        $result = null;

        foreach ($keys as $key)
        {
            if (!empty($data[ $key ]))
            {
                $result = $data[ $key ];
                break;
            }
        }

        return $result;
    }

    public function getLang($code, $replaces = null) : string
    {
		return Main\Localization\Loc::getMessage(static::$langPrefix . $code, $replaces) ?: $code;
    }

    public function getProvider()
    {
	    if ($this->provider === null)
	    {
		    $className = $this->arParams['PROVIDER_CLASS_NAME'];

		    Pay\Reference\Assert::notNull($className, 'params[PROVIDER_CLASS_NAME]');
		    Pay\Reference\Assert::isSubclassOf($className, Pay\Component\Reference\Form::class);

		    $this->provider = new $className($this);
	    }

	    return $this->provider;
    }

    protected function getValueByChain($item, $keyChain)
    {
	    return Pay\Utils\BracketChain::get($item, $keyChain);
    }

    protected function setValueByChain(&$item, $keyChain, $value) : void
    {
	    Pay\Utils\BracketChain::set($item, $keyChain, $value);
    }

    protected function splitFieldNameToChain($key) : array
    {
        return Pay\Utils\BracketChain::splitKey($key);
    }
}