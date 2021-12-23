<?php

namespace YandexPay\Pay\Components;

use Bitrix\Main;
use YandexPay\Pay;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

class AdminGrid extends \CBitrixComponent
{
    protected static $langPrefix = 'YANDEXPAY_PAY_COMPONENT_GRID_';

    /** @var Pay\Component\Reference\Grid */
    protected $provider;
    protected $viewList;
    protected $viewFilter;
    protected $viewSort;

    public function onPrepareComponentParams($arParams) : array
    {
        $arParams['GRID_ID'] = trim($arParams['GRID_ID']);
        $arParams['SUBLIST'] = ($arParams['SUBLIST'] === 'Y');
        $arParams['SUBLIST_TARGET'] = ($arParams['SUBLIST_TARGET'] === 'Y');
        $arParams['USE_FILTER'] = (!$arParams['SUBLIST'] && $arParams['USE_FILTER'] !== 'N');
        $arParams['LIST_FIELDS'] = (array)$arParams['LIST_FIELDS'];
        $arParams['FILTER_FIELDS'] = (array)$arParams['FILTER_FIELDS'];
        $arParams['DEFAULT_LIST_FIELDS'] = (array)$arParams['DEFAULT_LIST_FIELDS'];
        $arParams['DEFAULT_FILTER_FIELDS'] = (array)$arParams['DEFAULT_FILTER_FIELDS'];
        $arParams['CONTEXT_MENU'] = (array)$arParams['CONTEXT_MENU'];
        $arParams['CONTEXT_MENU_EXCEL'] = ($arParams['CONTEXT_MENU_EXCEL'] === 'Y');
        $arParams['CONTEXT_MENU_SETTINGS'] = ($arParams['CONTEXT_MENU_SETTINGS'] !== 'N');
        $arParams['TITLE'] = trim($arParams['TITLE']);
        $arParams['NAV_TITLE'] = trim($arParams['NAV_TITLE']);
        $arParams['EDIT_URL'] = trim($arParams['EDIT_URL']);
        $arParams['ROW_ACTIONS'] = (array)$arParams['ROW_ACTIONS'];
        $arParams['GROUP_ACTIONS'] = (array)$arParams['GROUP_ACTIONS'];
        $arParams['GROUP_ACTIONS_PARAMS'] = (array)$arParams['GROUP_ACTIONS_PARAMS'];
        $arParams['UI_GROUP_ACTIONS'] = isset($arParams['UI_GROUP_ACTIONS']) ? (array)$arParams['UI_GROUP_ACTIONS'] : null;
        $arParams['UI_GROUP_ACTIONS_PARAMS'] = isset($arParams['UI_GROUP_ACTIONS_PARAMS']) ? (array)$arParams['UI_GROUP_ACTIONS_PARAMS'] : null;
        $arParams['PRIMARY'] = !empty($arParams['PRIMARY']) ? (array)$arParams['PRIMARY'] : [ 'ID' ];
		$arParams['ALLOW_SAVE'] = !isset($arParams['ALLOW_SAVE']) || $arParams['ALLOW_SAVE'];
		$arParams['PAGER_LIMIT'] = isset($arParams['PAGER_LIMIT']) ? (int)$arParams['PAGER_LIMIT'] : null;
        $arParams['PROVIDER_CLASS_NAME'] = trim($arParams['PROVIDER_CLASS_NAME']);

        return $arParams;
    }

    public function executeComponent()
    {
        global $APPLICATION;

        try
        {
	        $this->setTitle();
	        $this->loadModules();

	        $this->prepareParams();
	        $this->initResult();

	        $this->checkParams();

	        $this->loadFields();
	        $this->loadFilter();

	        if ($this->canHandleRequest() && ($this->processAction() || $this->hasAjaxRequest()))
	        {
	            $APPLICATION->RestartBuffer();
	        }

            $this->buildHeaders();

	        $queryParams = [];
	        $queryParams += $this->initFilter();
	        $queryParams += $this->initSelect();
	        $queryParams += $this->initPager($queryParams);
	        $queryParams += $this->initSort();

	        if ($this->isExportMode())
	        {
				$this->loadAll($queryParams);
	        }
	        else
	        {
		        $this->loadItems($queryParams);

		        if ($this->isNeedResetQueryParams($queryParams))
		        {
					$queryParams = $this->resetQueryParams($queryParams);
			        $this->loadItems($queryParams);
		        }
	        }

	        $this->buildContextMenu();
	        $this->buildRows();
			$this->buildNavString($queryParams);
			$this->buildGroupActions();

	        $this->resolveTemplateName();
	        $this->includeComponentTemplate();
        }
	    catch (Main\SystemException $exception)
	    {
	        $this->addError($exception->getMessage());

		    $this->includeComponentTemplate('exception');
	    }
    }

    protected function canHandleRequest() : bool
    {
        return (
            !$this->arParams['SUBLIST']
            || $this->arParams['SUBLIST_TARGET']
        );
    }

    protected function processAction() : bool
    {
        $viewList = $this->getViewList();
	    $ids = $viewList->GroupAction();
	    $action = $ids ? $this->getViewListAction($viewList) : null;
        $result = false;

		if ($action !== null)
		{
			$result = true;

	        try
	        {
	        	if (!$this->arParams['ALLOW_SAVE'])
				{
					throw new Main\SystemException($this->getLang('ACTION_DISALLOW'));
				}

	            $actionData = [
	                'ID' => $ids,
	                'IS_ALL' => false
	            ];

	            if ($this->isViewListActionToAll($viewList))
	            {
	                $filter = $this->initFilter();

	                $actionData['IS_ALL'] = true;
	                $actionData['FILTER'] = $filter['filter'] ?? null;
	            }

	            $provider = $this->getProvider();
	            $provider->processAjaxAction($action, $actionData);
	        }
	        catch (Main\SystemException $exception)
	        {
	            $this->addError($exception->getMessage());
	        }
        }

        return $result;
    }

    protected function getViewListAction(\CAdminList $viewList) : ?string
    {
    	$result = null;

	    if (method_exists($viewList, 'GetAction'))
	    {
		    $result = $viewList->GetAction();
	    }
	    else if (isset($_REQUEST['action_button']) && $_REQUEST['action_button'] !== '')
	    {
		    $result = $_REQUEST['action_button'];
	    }
	    else if (isset($_REQUEST['action']))
	    {
		    $result = $_REQUEST['action'];
	    }

	    return $result;
    }

    protected function isViewListActionToAll(\CAdminList $viewList) : bool
    {
    	$result = false;
    	$uiGridRequestKey = 'action_all_rows_' . $viewList->table_id;

    	if (method_exists($viewList, 'IsGroupActionToAll'))
	    {
	    	$result = $viewList->IsGroupActionToAll();
	    }
    	else if (isset($_REQUEST['action_target']))
	    {
		    $result = ($_REQUEST['action_target'] === 'selected');
	    }
    	else if (isset($_REQUEST[$uiGridRequestKey]))
	    {
	    	$result = ($_REQUEST[$uiGridRequestKey] === 'Y');
	    }

    	return $result;
    }

    protected function hasAjaxRequest() : bool
    {
    	$isTargetList = ($this->request->get('table_id') === $this->arParams['GRID_ID'] || !$this->isSubList());
    	$requestMode = $this->request->get('mode');

        return (
			$isTargetList
			&& (
				$requestMode === 'excel'
				|| ($this->request->isAjaxRequest() && $requestMode !== null)
			)
		);
    }

    protected function initResult() : void
    {
        $this->arResult['CONTEXT_MENU'] = [];
        $this->arResult['FIELDS'] = [];
        $this->arResult['FILTER'] = [];
        $this->arResult['ITEMS'] = [];
        $this->arResult['TOTAL_COUNT'] = null;
        $this->arResult['ERRORS'] = [];
        $this->arResult['WARNINGS'] = [];
        $this->arResult['REDIRECT'] = null;
    }

    protected function getRequiredParams() : array
    {
        $provider = $this->getProvider();

        return [ 'GRID_ID' ] + $provider->getRequiredParams();
    }

	protected function prepareParams() : void
	{
		$this->arParams = $this->getProvider()->prepareComponentParams($this->arParams);
	}

    protected function checkParams() : void
    {
        $requiredParams = $this->getRequiredParams();

        foreach ($requiredParams as $paramKey)
        {
            if (empty($this->arParams[ $paramKey ]))
            {
                $message = $this->getLang('PARAM_REQUIRE', array(
                    '#PARAM#' => $paramKey
                ));

                throw new Main\ArgumentException($message);
            }
        }
    }

    protected function loadModules() : void
    {
        foreach ($this->getRequiredModules() as $module)
        {
	        $this->loadModule($module);
        }
    }

    protected function loadModule(string $module) : void
    {
        if (!Main\Loader::includeModule($module))
        {
            $message = $this->getLang('MODULE_REQUIRE', [
                '#MODULE#' => $module
            ]);

            throw new Main\SystemException($message);
        }
    }

	protected function getRequiredModules() : array
	{
		return array_merge(
			$this->getProvider()->getRequiredModules(),
			[ 'yandexpay.pay' ]
		);
	}

    public function setRedirectUrl(string $url) : void
	{
		$this->arResult['REDIRECT'] = $url;
	}

    public function addWarning(string $message) : void
    {
        $this->arResult['WARNINGS'][] = $message;
    }

    public function hasWarnings() : bool
    {
        return !empty($this->arResult['WARNINGS']);
    }

    public function getWarnings() : array
    {
    	return (array)$this->arResult['WARNINGS'];
    }

    public function showWarnings() : void
    {
        \CAdminMessage::ShowMessage([
            'TYPE' => 'ERROR',
            'MESSAGE' => implode('<br />', $this->arResult['WARNINGS']),
            'HTML' => true
        ]);
    }

    public function addError(string $message) : void
    {
        $this->arResult['ERRORS'][] = $message;
    }

    public function hasErrors() : bool
    {
        return !empty($this->arResult['ERRORS']);
    }

    public function getErrors() : array
    {
    	return (array)$this->arResult['ERRORS'];
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

        if ($this->arParams['TITLE'] !== '')
        {
            $APPLICATION->SetTitle($this->arParams['TITLE']);
        }
    }

    protected function loadFields() : void
    {
    	$provider = $this->getProvider();
        $select = $this->arParams['LIST_FIELDS'];

        $fields = $provider->getFields($select);
		$fields = $this->extendFields($fields);

		$this->arResult['FIELDS'] = $fields;
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

    protected function initFilter() : array
    {
        $provider = $this->getProvider();
        $defaultFilter = $provider->getDefaultFilter();
        $result = [];

		if (!empty($defaultFilter))
		{
			$result['filter'] = $defaultFilter;
		}
		else if (!empty($this->arParams['DEFAULT_FILTER']))
		{
			$result['filter'] = (array)$this->arParams['DEFAULT_FILTER'];
		}

        if (!$this->arParams['USE_FILTER'])
        {
            return $result;
        }

	    $listView = $this->getViewList();

        if ($listView instanceof \CAdminUiList)
        {
	        $result = $this->initFilterFromAdminList($listView, $result);
        }
        else
        {
        	$filterRequest = $listView->getFilter();
        	$result = $this->initFilterFromRequest($filterRequest, $result);
        }

        return $result;
    }

    protected function initFilterFromAdminList(\CAdminUiList $listView, array $defaultParameters) : array
    {
    	$listFilter = [];
    	$fieldsMap = array_column($this->arResult['FILTER'], 'fieldName', 'id');
    	$result = $defaultParameters;

	    $listView->AddFilter($this->arResult['FILTER'], $listFilter);

	    foreach ($listFilter as $filterKey => $filterValue)
	    {
	    	if (!preg_match('/^(.*?)(find_.+)$/', $filterKey, $matches)) { continue; }

	    	[, $filterCompare, $filterId] = $matches;

	    	if (isset($fieldsMap[$filterId]))
		    {
		    	$filterField = $fieldsMap[$filterId];

			    if (!isset($result['filter']))
			    {
				    $result['filter'] = [];
			    }

			    $result['filter'][$filterCompare . $filterField] = $filterValue;
		    }
	    }

	    return $result;
    }

    protected function initFilterFromRequest(array $request, array $defaultParameters) : array
    {
        $result = $defaultParameters;

        foreach ($this->arResult['FILTER'] as &$filter)
        {
            switch ($filter['type'])
            {
	            case 'number':
	            case 'date':

	                $fromRequestKey = $filter['id'] . '_from';
	                $hasFromRequest = (isset($request[$fromRequestKey]) && $request[$fromRequestKey] !== '');
	                $toRequestKey = $filter['id'] . '_to';
	                $hasToRequest = (isset($request[$toRequestKey]) && $request[$toRequestKey] !== '');
	                $filter['value'] = [
                        'from' => $hasFromRequest ? htmlspecialcharsbx($request[$fromRequestKey]) : '',
                        'to' => $hasToRequest ? htmlspecialcharsbx($request[$toRequestKey]) : ''
                    ];

	                if ($hasFromRequest || $hasToRequest)
	                {
	                    if (!isset($result['filter']))
		                {
		                    $result['filter'] = [];
		                }

		                if ($hasFromRequest)
		                {
		                    $result['filter']['>=' . $filter['fieldName']] = $request[$fromRequestKey];
		                }

		                if ($hasToRequest)
		                {
		                    $result['filter']['<=' . $filter['fieldName']] = $request[$toRequestKey];
		                }
	                }

	            break;

	            default:

	                if (isset($request[$filter['id']]) && $request[$filter['id']] !== '')
		            {
		                $filterRequest = $request[$filter['id']];

		                $filter['value'] = htmlspecialcharsbx($filterRequest);

		                if (!isset($result['filter']))
		                {
		                    $result['filter'] = [];
		                }

		                $result['filter'][$filter['fieldName']] = $filterRequest;
		            }

	            break;
            }
        }
        unset($filter);

	    return $result;
    }

    protected function initSelect() : array
    {
        $view = $this->getViewList();

	    return [
            'select' => $view->GetVisibleHeaderColumns()
        ];
    }

    protected function initPager(array $queryParams) : array
    {
        $result = [];

        if ($this->isSubList())
        {
            if ($this->isSubListAjaxPage())
            {
                $this->fillEmptyPager();
            }

            $navSize = \CAdminSubResult::GetNavSize(
                $this->arParams['GRID_ID'],
                20,
                $this->arParams['AJAX_URL']
            );
        }
        else if ($this->useUiView())
        {
	        $navSize = \CAdminUiResult::GetNavSize($this->arParams['GRID_ID']);
        }
        else
        {
            $navSize = \CAdminResult::GetNavSize($this->arParams['GRID_ID']);
        }

        if ($this->arParams['PAGER_LIMIT'] > 0 && $navSize > $this->arParams['PAGER_LIMIT'])
        {
	        $navSize = $this->arParams['PAGER_LIMIT'];
        }

	    /** @noinspection PhpMultipleClassDeclarationsInspection */
	    $navParams = \CDBResult::GetNavParams($navSize);

		if (!$navParams['SHOW_ALL'])
		{
			$page = (int)$navParams['PAGEN'];
			$pageSize = (int)$navParams['SIZEN'];

			$totalCount = $this->loadTotalCount($queryParams);

			if ($totalCount !== null)
			{
				$maxPageNum = max(1, ceil($totalCount / $pageSize));

				if ($page > $maxPageNum)
				{
					$page = $maxPageNum;
				}
			}

			$result['limit'] = $pageSize;
			$result['offset'] = $pageSize * ($page - 1);

			$this->arResult['TOTAL_COUNT'] = $totalCount;
		}

        return $result;
    }

    protected function fillEmptyPager() : void
    {
        global $NavNum;

        if ($NavNum === null) { $NavNum = 0; }

        for ($i = $NavNum + 1; $i < 10; $i++)
        {
			$requestKey = 'SIZEN_' . $i;

            if (isset($_REQUEST[$requestKey]))
            {
                $NavNum = $i - 1;
                break;
            }
        }
    }

    protected function initSort() : array
    {
	    $viewSort = $this->getViewSort();
	    $order = null;

	    if (!empty($GLOBALS[$viewSort->by_name]))
	    {
	    	$sortField = mb_strtoupper($GLOBALS[$viewSort->by_name]);

	    	if (isset($this->arResult['FIELDS'][$sortField]))
		    {
		        $sortOrder = (
		            isset($GLOBALS[$viewSort->ord_name]) && mb_strtoupper($GLOBALS[$viewSort->ord_name]) === 'DESC'
		                ? 'DESC'
		                : 'ASC'
		        );

		        $order = [
		            $sortField => $sortOrder
		        ];
		    }
	    }

	    if ($order === null)
	    {
	        $provider = $this->getProvider();
	        $order = $provider->getDefaultSort();
	    }

	    return [
	        'order' => $order
	    ];
    }

    protected function isExportMode() : bool
    {
    	$view = $this->getViewList();

    	return method_exists($view, 'isExportMode')
		    ? $view->isExportMode()
		    : (isset($_REQUEST['mode']) && $_REQUEST['mode'] === 'excel');
    }

    protected function loadAll(array $queryParams) : void
    {
	    $queryParams = array_diff_key($queryParams, [
	    	'limit' => true,
		    'offset' => true,
	    ]);

	    $this->loadItems($queryParams);
    }

    protected function loadItems(array $queryParams) : void
    {
	    $this->arResult['ITEMS'] = $this->queryItems($queryParams);
    }

    protected function queryItems(array $queryParams) : array
    {
	    if (!empty($queryParams['select']))
	    {
		    $queryParams['select'] = array_merge(
			    $queryParams['select'],
			    $this->arParams['PRIMARY']
		    );
	    }

	    $queryResult = $this->getProvider()->load($queryParams);

	    if (isset($queryResult['ITEMS']))
	    {
		    $rows = $queryResult['ITEMS'];

		    if (isset($queryResult['TOTAL_COUNT']))
		    {
			    $this->arResult['TOTAL_COUNT'] = $queryResult['TOTAL_COUNT'];
		    }
	    }
	    else
	    {
		    $rows = $queryResult;
	    }

	    return $rows;
    }

    protected function isNeedResetQueryParams(array $queryParams) : bool
    {
    	return (
    		empty($this->arResult['ITEMS'])
		    && $this->arResult['TOTAL_COUNT'] > 0
		    && $queryParams['offset'] >= $this->arResult['TOTAL_COUNT']
	    );
    }

    protected function resetQueryParams(array $queryParams) : array
    {
	    $queryParams['offset'] = 0;

	    return $queryParams;
    }

    protected function loadTotalCount(array $queryParams) : ?int
    {
	    return $this->getProvider()->loadTotalCount($queryParams);
    }

    protected function loadFilter() : void
    {
        if (!$this->arParams['USE_FILTER']) { return; }

        $useFieldsMap = array_flip($this->arParams['FILTER_FIELDS']);
        $defaultFieldsMap = array_flip($this->arParams['DEFAULT_FILTER_FIELDS']);
        $filterIdList = [];
        $filterDefaultIndexes = [];
        $filterIndex = 0;
        $useUiView = $this->useUiView();

        foreach ($this->arResult['FIELDS'] as $fieldName => $field)
        {
            if (
	            (!empty($useFieldsMap) && !isset($useFieldsMap[$fieldName]))
	            || (isset($field['FILTERABLE']) && $field['FILTERABLE'] === false)
	            || $field['USER_TYPE']['BASE_TYPE'] === 'file'
            )
            {
            	continue;
            }

			$hasClassName = !empty($field['USER_TYPE']['CLASS_NAME']);
            $item = [
                'id' => 'find_' . mb_strtolower($fieldName),
                'fieldName' => $fieldName,
                'value' => null,
                'name' => $this->getFirstNotEmpty($field, array('LIST_COLUMN_LABEL', 'EDIT_FORM_LABEL', 'LIST_FILTER_LABEL')),
	            'filterable' => '',
            ];

			if ($field['USER_TYPE']['BASE_TYPE'] === 'list' && !empty($field['VALUES']))
            {
	            $item['type'] = 'list';
	            $item['items'] = [];

	            foreach ($field['VALUES'] as $option)
	            {
	            	$item['items'][$option['ID']] = $option['VALUE'];
	            }

	            $filterIdList[] = $item['id'];
            }
			else if ($hasClassName && is_callable(array($field['USER_TYPE']['CLASS_NAME'], 'GetList')))
            {
                $item['type'] = 'list';
                $item['items'] = [];

                $query = call_user_func(array($field['USER_TYPE']['CLASS_NAME'], 'GetList'), $field);

                if (is_array($query))
                {
	                foreach ($query as $option)
	                {
		                $item['items'][$option['ID']] = $option['VALUE'];
	                }
                }
                else if ($query)
                {
	                while ($option = $query->Fetch())
	                {
		                $item['items'][$option['ID']] = $option['VALUE'];
	                }
                }

                $filterIdList[] = $item['id'];
            }
            else if ($field['USER_TYPE']['BASE_TYPE'] === 'datetime')
            {
                $item['type'] = 'date';

                $filterIdList[] = $item['id'] . '_from';
            	$filterIdList[] = $item['id'] . '_to';
            }
            else if ($field['USER_TYPE']['USER_TYPE_ID'] !== 'boolean' && in_array($field['USER_TYPE']['BASE_TYPE'], ['int', 'double'], true))
            {
            	$item['type'] = 'number';

            	$filterIdList[] = $item['id'] . '_from';
            	$filterIdList[] = $item['id'] . '_to';
            }
            else if ($hasClassName && !$useUiView && is_callable([$field['USER_TYPE']['CLASS_NAME'], 'GetFilterHTML']))
            {
                $item['type'] = 'custom';

                $filterIdList[] = $item['id'];
            }
            else if ($useUiView && $field['USER_TYPE']['USER_TYPE_ID'] === 'boolean')
            {
            	$item['type'] = 'list';
	            $item['items'] = [
	            	'1' => Main\Localization\Loc::getMessage('MAIN_YES'),
		            '0' => Main\Localization\Loc::getMessage('MAIN_NO'),
	            ];
            }
            else
            {
                $item['type'] = 'string';

                $filterIdList[] = $item['id'];
            }

            $this->arResult['FILTER'][$fieldName] = $item;

            if (isset($defaultFieldsMap[$fieldName]))
            {
            	$filterDefaultIndexes[$fieldName] = $filterIndex;
            }

            ++$filterIndex;
        }

        if (empty($filterDefaultIndexes) && !empty($this->arResult['FILTER']))
        {
        	reset($this->arResult['FILTER']);

        	$firstKey = key($this->arResult['FILTER']);
        	$filterDefaultIndexes[$firstKey] = 0;
        }

        $this->getViewList()->InitFilter($filterIdList);

        if ($this->useUiView())
        {
        	foreach ($filterDefaultIndexes as $fieldName => $fieldIndex)
	        {
		        $this->arResult['FILTER'][$fieldName]['default'] = true;
	        }
        }
        else
        {
            $this->getViewFilter()->SetDefaultRows(array_values($filterDefaultIndexes));
        }
    }

    public function getFilterHtml(array $filter) : string
    {
        $field = $this->arResult['FIELDS'][$filter['fieldName']];

        return call_user_func(
            [ $field['USER_TYPE']['CLASS_NAME'], 'GetFilterHTML' ],
            $field,
			[
				'NAME' => $filter['id'],
				'VALUE' => $filter['value'],
				'TABLE_ID' => $this->arParams['GRID_ID'] . '_filter',
			]
		);
    }

	protected function buildContextMenu() : void
    {
    	$menuItems = isset($this->arParams['CONTEXT_MENU']) ? (array)$this->arParams['CONTEXT_MENU'] : [];
	    $menuItems = array_merge($menuItems, $this->provider->getContextMenu());

		if (
			!empty($menuItems)
			|| $this->arParams['CONTEXT_MENU_EXCEL']
			|| $this->arParams['CONTEXT_MENU_SETTINGS']
		)
		{
			$view = $this->getViewList();
			$menuItems = $this->makeContextActions($menuItems);

			$view->AddAdminContextMenu($menuItems, $this->arParams['CONTEXT_MENU_EXCEL'], $this->arParams['CONTEXT_MENU_SETTINGS']);
		}
    }

	protected function makeContextActions(array $actions) : array
	{
		foreach ($actions as &$action)
		{
			$type = $action['TYPE'] ?? 'UNKNOWN';
			$action = $this->makeAction($type, $action);
		}
		unset($action);

		return $actions;
	}

    protected function buildHeaders() : void
    {
        $defaultFieldsMap = array_flip($this->arParams['DEFAULT_LIST_FIELDS']);
        $headers = [];
        $view = $this->getViewList();

        foreach ($this->arResult['FIELDS'] as $fieldName => $field)
        {
        	if (isset($field['SELECTABLE']) && $field['SELECTABLE'] === false) { continue; }

            $headers[$fieldName] = [
                'id' => $fieldName,
                'content' => $this->getFirstNotEmpty($field, array('LIST_COLUMN_LABEL', 'EDIT_FORM_LABEL', 'LIST_FILTER_LABEL')),
                'sort' => !isset($field['SORTABLE']) || $field['SORTABLE'] ? $fieldName : null,
                'first_order' => 'asc',
                'default' => empty($defaultFieldsMap) || isset($defaultFieldsMap[$fieldName])
            ];
        }

        $view->AddHeaders($headers);
    }

    protected function buildRows() : void
    {
        if (!empty($this->arResult['ITEMS']))
        {
            $view = $this->getViewList();
            $headers = $view->GetVisibleHeaderColumns();
            $provider = $this->getProvider();

            foreach ($this->arResult['ITEMS'] as $item)
            {
                $link = null;
                $actions = $this->buildRowActions($item);
                $actions = $provider->filterActions($item, $actions);
                $defaultActions = array_filter($actions, static function ($action) { return $action['DEFAULT'] === true; });
	            $defaultAction = reset($defaultActions);
	            $editUrl = $this->getRowEditUrl($item);

	            if ($defaultAction !== false)
	            {
	            	if (
						isset($defaultAction['URL'])
						&& (
							empty($defaultAction['ACTION'])
							|| preg_match('/BX.adminPanel.Redirect/', $defaultAction['ACTION'])
						)
		            )
		            {
			            $link = $defaultAction['URL'];
			            $item['ROW_URL'] = $defaultAction['URL'];
		            }
	            	else
		            {
			            $item['ROW_URL'] = $editUrl;
		            }
	            }
	            else if ((string)$editUrl !== '')
	            {
		            $link = $editUrl;
		            $item['ROW_URL'] = $editUrl;
	            }

                $viewRow = $view->AddRow($item['ID'], [], $link);

                foreach ($headers as $fieldName)
                {
                    $viewRow->AddViewField($fieldName, $this->buildRowValue($item, $fieldName));
                }

				if (!empty($actions))
				{
                    $viewRow->AddActions($actions);
                }

				if (!empty($item['DISABLED']))
				{
					$viewRow->bReadOnly = true;
				}
            }
        }
    }

    protected function getRowEditUrl(array $item)
    {
    	$itemType = $item['ROW_TYPE'] ?? 'DEFAULT';
    	$parameterPrefix = $itemType !== 'DEFAULT' ? $itemType . '_' : '';
	    $parameterName = $parameterPrefix  . 'EDIT_URL';
	    $result = null;

	    if (isset($this->arParams[$parameterName]))
	    {
	    	$result = (string)$this->arParams[$parameterName];
	    	$replaces = array_intersect_key($item, [
	    		'ID' => true,
			    'PRIMARY' => true,
		    ]);

	    	foreach ($replaces as $key => $value)
		    {
			    $result = str_replace('#' . $key . '#', $value, $result);
		    }
	    }

    	return $result;
    }

    protected function buildRowValue(array $item, string $fieldKey)
    {
	    $field = $this->arResult['FIELDS'][$fieldKey] ?? null;

	    if ($field === null || !$this->isMatchRowType($item, $field)) { return null; }

	    $result = null;

		if (isset($field['USER_TYPE']['CLASS_NAME']))
        {
            $result = $this->buildRowValueFromUserField($field, $item[$fieldKey], $item);
        }
        else if (isset($item[$fieldKey]))
        {
            $result = $item[$fieldKey];
        }

        return $result;
    }

    protected function buildRowValueFromUserField($userField, $value, $row) : string
    {
    	return Pay\Ui\UserField\Helper\Renderer::getViewHtml($userField, $value, $row);
    }

    protected function buildRowActions(array $item) : array
    {
        return !empty($this->arParams['ROW_ACTIONS'])
	        ? $this->makeRowActions($item, $this->arParams['ROW_ACTIONS'])
	        : [];
    }

	protected function makeRowActions(array $item, array $actions) : array
	{
		$result = [];

		foreach ($actions as $type => $action)
		{
			if (!$this->isMatchRowType($item, $action)) { continue; }

			if (isset($action['MENU']))
			{
				$result[] = array_filter([
					'ICON' => $action['ICON'] ?? null,
					'DEFAULT' => $action['DEFAULT'] ?? null,
					'FILTER' => $action['FILTER'] ?? null,
					'TEXT' => $action['TEXT'],
					'TYPE' => $type,
					'MENU' => $this->makeRowActions($item, $action['MENU']),
				]);

				continue;
			}

			$result[] = $this->makeAction($type, $action, $item);
		}

		return $result;
	}

    protected function isMatchRowType(array $item, array $target) : bool
    {
    	$itemType = $item['ROW_TYPE'] ?? 'DEFAULT';
    	$targetType = $target['ROW_TYPE'] ?? 'DEFAULT';

    	if (is_array($targetType))
	    {
	    	$result = in_array($itemType, $targetType, true);
	    }
    	else
	    {
		    $result = ($itemType === $targetType);
	    }

    	return $result;
    }

	protected function makeAction(string $type, array $action, array $item = null) : array
	{
		global $APPLICATION;

		$replacesFrom = [];
		$replacesTo = [];

		if ($item !== null)
		{
			foreach ($item as $key => $value)
			{
				if (is_scalar($value))
				{
					$replacesFrom[] ='#' . $key . '#';
					$replacesTo[] = $value;
				}
			}
		}

		$actionUrl = null;

		if (isset($action['METHOD']))
		{
			$actionMethod = str_replace($replacesFrom, $replacesTo, $action['METHOD']);
		}
		else if ($type === 'DELETE' || isset($action['ACTION']))
		{
			$actionMethod = $action['ACTION'] ?? 'delete';
			$actionPrimaryKey = $this->isSubList() ? 'SUB_ID' : 'ID';

			$queryParams = [
				'sessid' => bitrix_sessid(),
				'action_button' => $actionMethod,
				$actionPrimaryKey => $item['ID'],
			];

			if ($this->useUiView())
			{
				$queryParams['action'] = $actionMethod;
				unset($queryParams['action_button']);

				$actionMethod = sprintf(
					'BX.Main.gridManager.getById("%s").instance.reloadTable("POST", %s)',
					$this->arParams['GRID_ID'],
					Main\Web\Json::encode($queryParams)
				);
			}
			else
			{
				if (!empty($this->arParams['AJAX_URL']))
				{
					$url =
						$this->arParams['AJAX_URL']
						. (mb_strpos($this->arParams['AJAX_URL'], '?') === false ? '?' : '&')
						. http_build_query($queryParams);
				}
				else
				{
					$url = $APPLICATION->GetCurPageParam(
						http_build_query($queryParams),
						array_keys($queryParams)
					);
				}

				$actionMethod = $this->arParams['GRID_ID'] . '.GetAdminList("' . \CUtil::addslashes($url) . '");';
			}
		}
		else
		{
			if (isset($action['QUERY']))
			{
				$actionUrlQueryParameters = $action['QUERY'];

				foreach ($actionUrlQueryParameters as &$actionUrlQueryParameter)
				{
					$actionUrlQueryParameter = str_replace($replacesFrom, $replacesTo, $actionUrlQueryParameter);
				}
				unset($actionUrlQueryParameter);

				if (!empty($this->arParams['AJAX_URL']))
				{
					$actionUrl =
						$this->arParams['AJAX_URL']
						. (mb_strpos($this->arParams['AJAX_URL'], '?') === false ? '?' : '&')
						. http_build_query($actionUrlQueryParameters);
				}
				else
				{
					$actionUrl = $APPLICATION->GetCurPageParam(
						http_build_query($actionUrlQueryParameters),
						array_merge(
							array_keys($actionUrlQueryParameters),
							$this->getUrlSystemParameters()
						),
						false
					);
				}
			}
			else
			{
				$actionUrl = str_replace($replacesFrom, $replacesTo, $action['URL'] ?? $action['LINK']);
			}

			if (mb_strpos($actionUrl, 'lang=') === false)
			{
				$actionUrl .=
					(mb_strpos($actionUrl, '?') === false ? '?' : '&')
					. 'lang=' . LANGUAGE_ID;
			}

			if (isset($action['MODAL']) && $action['MODAL'] === 'Y')
			{
				$modalParameters = array_merge(
					[ 'content_url' => $actionUrl ],
					isset($action['MODAL_PARAMETERS']) ? (array)$action['MODAL_PARAMETERS'] : [],
					[
						'draggable' => true,
						'resizable' => true,
					]
				);

				if (isset($action['MODAL_TITLE']))
				{
					$modalParameters['title'] = str_replace($replacesFrom, $replacesTo, $action['MODAL_TITLE']);
				}

				$actionMethod = sprintf(
					'(new BX.CAdminDialog(%s)).Show();',
					\CUtil::PhpToJSObject($modalParameters)
				);
			}
			else if (isset($action['MODAL_FORM']) && $action['MODAL_FORM'] === 'Y')
			{
				Main\UI\Extension::load('yandexpaypay.admin.ui.modalform');

				$modalParameters = array_merge(
					[ 'url' => $actionUrl, 'unescapeUrl' => true ],
					(array)($action['MODAL_PARAMETERS'] ?? [])
				);

				if (isset($action['MODAL_TITLE']))
				{
					$modalParameters['title'] = str_replace($replacesFrom, $replacesTo, $action['MODAL_TITLE']);
				}

				$actionMethod = sprintf(
					'(new BX.YandexPay.Ui.ModalForm(null, %s)).activate();',
					\CUtil::PhpToJSObject($modalParameters)
				);
			}
			else if (isset($action['WINDOW']) && $action['WINDOW'] === 'Y')
			{
				$actionMethod = 'jsUtils.OpenWindow("' . \CUtil::AddSlashes($actionUrl) . '", 1250, 800);';
			}
			else
			{
				$actionMethod = "BX.adminPanel.Redirect([], '".\CUtil::AddSlashes($actionUrl)."', event);";
			}
		}

		if (!empty($action['CONFIRM']))
		{
			$confirmMessage = !empty($action['CONFIRM_MESSAGE']) ? $action['CONFIRM_MESSAGE'] : $this->getLang('ROW_ACTION_CONFIRM');
			$actionMethod = 'if (confirm("' . \CUtil::AddSlashes($confirmMessage) . '")) ' . $actionMethod;
		}

		return array_filter([
			'URL' => $actionUrl,
			'ACTION' => $actionMethod,
			'ONCLICK' => $actionMethod, // submenu for main.ui.grid
			'ICON' => $action['ICON'] ?? null,
			'DEFAULT' => $action['DEFAULT'] ?? null,
			'FILTER' => $action['FILTER'] ?? null,
			'TEXT' => $action['TEXT'],
			'TYPE' => $type,
		]);
	}

    protected function buildNavString(array $queryParams) : void
    {
        $listView = $this->getViewList();

        if ($this->isSubList())
        {
            $iterator = new \CAdminSubResult([], $this->arParams['GRID_ID'], $listView->GetListUrl(true));
        }
        else if ($this->useUiView())
        {
	        $iterator = new \CAdminUiResult([], $this->arParams['GRID_ID']);
        }
        else
        {
            $iterator = new \CAdminResult([], $this->arParams['GRID_ID']);
        }

		if (isset($queryParams['limit']))
		{
			$page = floor($queryParams['offset'] / $queryParams['limit']) + 1;
			$totalCount = $this->arResult['TOTAL_COUNT'];
			$totalPages = ceil($totalCount / $queryParams['limit']);

			$iterator->NavStart($queryParams['limit'], true, $page);
			$iterator->NavRecordCount = $totalCount;
			$iterator->NavPageCount = $totalPages;
			$iterator->NavPageNomer = $page;
		}
		else
		{
			$iterator->NavStart();
		}

		if ($listView instanceof \CAdminUiList)
		{
			$listView->SetNavigationParams($iterator, [
				'BASE_LINK' => $this->getBaseUrl(),
			]);
		}
		else
		{
			$listView->NavText($iterator->GetNavPrint($this->arParams['NAV_TITLE']));
        }
    }

    protected function buildGroupActions() : void
    {
    	$useUiView = $this->useUiView();
	    $actions = $useUiView && isset($this->arParams['UI_GROUP_ACTIONS'])
		    ? (array)$this->arParams['UI_GROUP_ACTIONS']
		    : $this->arParams['GROUP_ACTIONS'];
	    $actions += $useUiView ? $this->provider->getUiGroupActions() : $this->provider->getGroupActions();

	    if (!empty($actions))
		{
			$params = $useUiView && isset($this->arParams['UI_GROUP_ACTIONS_PARAMS'])
				? (array)$this->arParams['UI_GROUP_ACTIONS_PARAMS']
				: (array)$this->arParams['GROUP_ACTIONS_PARAMS'];
			$params += $useUiView ? $this->provider->getUiGroupActionParams() : $this->provider->getGroupActionParams();

			if (
				$useUiView
				&& !isset($actions['for_all'])
				&& (!isset($params['disable_action_target']) || $params['disable_action_target'] !== true)
			)
			{
				$actions['for_all'] = true;
			}

			$viewList = $this->getViewList();
			$viewList->AddGroupActionTable($actions, $params);
		}
    }

    protected function getFirstNotEmpty(array $data, array $keys)
    {
        $result = null;

        foreach ($keys as $key)
        {
            if (!empty($data[ $key ]))
            {
                $result = $data[ $key ];
            }
        }

        return $result;
    }

    public function getViewList() : \CAdminList
    {
        if ($this->viewList === null)
        {
            if ($this->isSubList())
            {
                $this->viewList = new \CAdminSubList(
                    $this->arParams['GRID_ID'],
                    false, //$this->getViewSort(), sort inside class
                    $this->arParams['AJAX_URL']
                );
            }
            else if ($this->useUiView())
            {
            	$this->viewList = new \CAdminUiList(
	                $this->arParams['GRID_ID'],
                    $this->getViewSort()
	            );
	        }
            else
            {
	            $this->viewList = new \CAdminList(
		            $this->arParams['GRID_ID'],
		            $this->getViewSort()
	            );
            }
        }

        return $this->viewList;
    }

    public function getViewSort() : \CAdminSorting
    {
        if ($this->viewSort === null)
        {
            $this->viewSort = $this->useUiView() && class_exists(\CAdminUiSorting::class)
	            ? new \CAdminUiSorting($this->arParams['GRID_ID'])
                : new \CAdminSorting($this->arParams['GRID_ID']);
        }

        return $this->viewSort;
    }

    public function getViewFilter() : \CAdminFilter
    {
        if ($this->viewFilter === null)
        {
            $this->viewFilter = new \CAdminFilter(
                $this->arParams['GRID_ID'] . '_filter',
                $this->getViewFilterPopup()
            );
        }

        return $this->viewFilter;
    }

    protected function getViewFilterPopup() : array
    {
        $result = [];

        foreach ($this->arResult['FILTER'] as $filter)
        {
            $result[] = $filter['name'];
        }

        return $result;
    }

    protected function resolveTemplateName() : void
    {
    	if ((string)$this->getTemplateName() === '' && $this->useUiView())
	    {
	    	$this->setTemplateName('ui');
	    }
    }

    protected function useUiView() : bool
    {
    	return !$this->isSubList() && $this->supportsUiView();
    }

    protected function supportsUiView() : bool
    {
    	return (
    		\class_exists(\CAdminUiList::class)
		    && \class_exists(\CAdminUiListActionPanel::class)
	    );
    }

    protected function isSubList() : bool
    {
        $result = false;

        if ($this->arParams['SUBLIST'] && Main\Loader::includeModule('iblock'))
        {
	        /** @noinspection PhpIncludeInspection */
	        require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/iblock/classes/general/subelement.php';

            $result = true;
        }

        return $result;
    }

    protected function isSubListAjaxPage() : bool
    {
        global $APPLICATION;

        $curPage = $APPLICATION->GetCurPage(false);

        return mb_strpos($this->arParams['AJAX_URL'], $curPage) === 0;
    }

    public function getUrl()
    {
        global $APPLICATION;

        $systemParameters = $this->getUrlSystemParameters();

        return $APPLICATION->GetCurPageParam('', $systemParameters);
    }

    public function getBaseUrl()
    {
        global $APPLICATION;

        return $this->arParams['BASE_URL'] ?: $APPLICATION->GetCurPage();
    }

    protected function getUrlSystemParameters() : array
    {
    	return array_merge(
    		Main\HttpRequest::getSystemParameters(),
            [ 'table_id', 'mode', 'grid_id', 'grid_action', 'bxajaxid', 'internal', 'clear_nav' ]
	    );
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
			Pay\Reference\Assert::isSubclassOf($className, Pay\Component\Reference\Grid::class);

            $this->provider = new $className($this);
        }

        return $this->provider;
    }
}