<?php

/* ORMENTITYANNOTATION:YandexPay\Pay\Trading\Setup\RepositoryTable */
namespace YandexPay\Pay\Trading\Setup {
	/**
	 * Model
	 * @see \YandexPay\Pay\Trading\Setup\RepositoryTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \YandexPay\Pay\Trading\Setup\Model setId(\int $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getSiteId()
	 * @method \YandexPay\Pay\Trading\Setup\Model setSiteId(\string $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \YandexPay\Pay\Trading\Setup\Model resetSiteId()
	 * @method \YandexPay\Pay\Trading\Setup\Model unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \string getPersonTypeId()
	 * @method \YandexPay\Pay\Trading\Setup\Model setPersonTypeId(\string $personTypeId)
	 * @method bool hasPersonTypeId()
	 * @method bool isPersonTypeIdFilled()
	 * @method bool isPersonTypeIdChanged()
	 * @method \string remindActualPersonTypeId()
	 * @method \string requirePersonTypeId()
	 * @method \YandexPay\Pay\Trading\Setup\Model resetPersonTypeId()
	 * @method \YandexPay\Pay\Trading\Setup\Model unsetPersonTypeId()
	 * @method \string fillPersonTypeId()
	 * @method \boolean getActive()
	 * @method \YandexPay\Pay\Trading\Setup\Model setActive(\boolean $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \YandexPay\Pay\Trading\Setup\Model resetActive()
	 * @method \YandexPay\Pay\Trading\Setup\Model unsetActive()
	 * @method \boolean fillActive()
	 * @method \YandexPay\Pay\Trading\Settings\Collection getSettings()
	 * @method \YandexPay\Pay\Trading\Settings\Collection requireSettings()
	 * @method \YandexPay\Pay\Trading\Settings\Collection fillSettings()
	 * @method bool hasSettings()
	 * @method bool isSettingsFilled()
	 * @method bool isSettingsChanged()
	 * @method void addToSettings(\YandexPay\Pay\Trading\Settings\Model $repository)
	 * @method void removeFromSettings(\YandexPay\Pay\Trading\Settings\Model $repository)
	 * @method void removeAllSettings()
	 * @method \YandexPay\Pay\Trading\Setup\Model resetSettings()
	 * @method \YandexPay\Pay\Trading\Setup\Model unsetSettings()
	 * @method \YandexPay\Pay\Injection\Setup\Collection getInjection()
	 * @method \YandexPay\Pay\Injection\Setup\Collection requireInjection()
	 * @method \YandexPay\Pay\Injection\Setup\Collection fillInjection()
	 * @method bool hasInjection()
	 * @method bool isInjectionFilled()
	 * @method bool isInjectionChanged()
	 * @method void addToInjection(\YandexPay\Pay\Injection\Setup\Model $repository)
	 * @method void removeFromInjection(\YandexPay\Pay\Injection\Setup\Model $repository)
	 * @method void removeAllInjection()
	 * @method \YandexPay\Pay\Trading\Setup\Model resetInjection()
	 * @method \YandexPay\Pay\Trading\Setup\Model unsetInjection()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \YandexPay\Pay\Trading\Setup\Model set($fieldName, $value)
	 * @method \YandexPay\Pay\Trading\Setup\Model reset($fieldName)
	 * @method \YandexPay\Pay\Trading\Setup\Model unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \YandexPay\Pay\Trading\Setup\Model wakeUp($data)
	 */
	class EO_Repository {
		/* @var \YandexPay\Pay\Trading\Setup\RepositoryTable */
		static public $dataClass = '\YandexPay\Pay\Trading\Setup\RepositoryTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace YandexPay\Pay\Trading\Setup {
	/**
	 * Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getSiteIdList()
	 * @method fillSiteId()
	 * @method \string[] getPersonTypeIdList()
	 * @method fillPersonTypeId()
	 * @method \boolean[] getActiveList()
	 * @method fillActive()
	 * @method \YandexPay\Pay\Trading\Settings\Collection[] getSettingsList()
	 * @method void fillSettings()
	 * @method \YandexPay\Pay\Injection\Setup\Collection[] getInjectionList()
	 * @method void fillInjection()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\YandexPay\Pay\Trading\Setup\Model $object)
	 * @method bool has(\YandexPay\Pay\Trading\Setup\Model $object)
	 * @method bool hasByPrimary($primary)
	 * @method \YandexPay\Pay\Trading\Setup\Model getByPrimary($primary)
	 * @method \YandexPay\Pay\Trading\Setup\Model[] getAll()
	 * @method bool remove(\YandexPay\Pay\Trading\Setup\Model $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \YandexPay\Pay\Trading\Setup\Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \YandexPay\Pay\Trading\Setup\Model current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Repository_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \YandexPay\Pay\Trading\Setup\RepositoryTable */
		static public $dataClass = '\YandexPay\Pay\Trading\Setup\RepositoryTable';
	}
}
namespace YandexPay\Pay\Trading\Setup {
	/**
	 * @method static EO_Repository_Query query()
	 * @method static EO_Repository_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_Repository_Result getById($id)
	 * @method static EO_Repository_Result getList(array $parameters = array())
	 * @method static EO_Repository_Entity getEntity()
	 * @method static \YandexPay\Pay\Trading\Setup\Model createObject($setDefaultValues = true)
	 * @method static \YandexPay\Pay\Trading\Setup\Collection createCollection()
	 * @method static \YandexPay\Pay\Trading\Setup\Model wakeUpObject($row)
	 * @method static \YandexPay\Pay\Trading\Setup\Collection wakeUpCollection($rows)
	 */
	class RepositoryTable extends \Bitrix\Main\ORM\Data\DataManager {}
	/**
	 * @method EO_Repository_Result exec()
	 * @method \YandexPay\Pay\Trading\Setup\Model fetchObject()
	 * @method \YandexPay\Pay\Trading\Setup\Collection fetchCollection()
	 */
	class EO_Repository_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \YandexPay\Pay\Trading\Setup\Model fetchObject()
	 * @method \YandexPay\Pay\Trading\Setup\Collection fetchCollection()
	 */
	class EO_Repository_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \YandexPay\Pay\Trading\Setup\Model createObject($setDefaultValues = true)
	 * @method \YandexPay\Pay\Trading\Setup\Collection createCollection()
	 * @method \YandexPay\Pay\Trading\Setup\Model wakeUpObject($row)
	 * @method \YandexPay\Pay\Trading\Setup\Collection wakeUpCollection($rows)
	 */
	class EO_Repository_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:YandexPay\Pay\Trading\Settings\RepositoryTable */
namespace YandexPay\Pay\Trading\Settings {
	/**
	 * Model
	 * @see \YandexPay\Pay\Trading\Settings\RepositoryTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \YandexPay\Pay\Trading\Settings\Model setId(\int $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getSetupId()
	 * @method \YandexPay\Pay\Trading\Settings\Model setSetupId(\int $setupId)
	 * @method bool hasSetupId()
	 * @method bool isSetupIdFilled()
	 * @method bool isSetupIdChanged()
	 * @method \int remindActualSetupId()
	 * @method \int requireSetupId()
	 * @method \YandexPay\Pay\Trading\Settings\Model resetSetupId()
	 * @method \YandexPay\Pay\Trading\Settings\Model unsetSetupId()
	 * @method \int fillSetupId()
	 * @method \string getName()
	 * @method \YandexPay\Pay\Trading\Settings\Model setName(\string $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string getValue()
	 * @method \YandexPay\Pay\Trading\Settings\Model setValue(\string $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
	 * @method \string remindActualValue()
	 * @method \string requireValue()
	 * @method \YandexPay\Pay\Trading\Settings\Model resetValue()
	 * @method \YandexPay\Pay\Trading\Settings\Model unsetValue()
	 * @method \string fillValue()
	 * @method \YandexPay\Pay\Trading\Setup\Model getSetup()
	 * @method \YandexPay\Pay\Trading\Setup\Model remindActualSetup()
	 * @method \YandexPay\Pay\Trading\Setup\Model requireSetup()
	 * @method \YandexPay\Pay\Trading\Settings\Model setSetup(\YandexPay\Pay\Trading\Setup\Model $object)
	 * @method \YandexPay\Pay\Trading\Settings\Model resetSetup()
	 * @method \YandexPay\Pay\Trading\Settings\Model unsetSetup()
	 * @method bool hasSetup()
	 * @method bool isSetupFilled()
	 * @method bool isSetupChanged()
	 * @method \YandexPay\Pay\Trading\Setup\Model fillSetup()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \YandexPay\Pay\Trading\Settings\Model set($fieldName, $value)
	 * @method \YandexPay\Pay\Trading\Settings\Model reset($fieldName)
	 * @method \YandexPay\Pay\Trading\Settings\Model unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \YandexPay\Pay\Trading\Settings\Model wakeUp($data)
	 */
	class EO_Repository {
		/* @var \YandexPay\Pay\Trading\Settings\RepositoryTable */
		static public $dataClass = '\YandexPay\Pay\Trading\Settings\RepositoryTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace YandexPay\Pay\Trading\Settings {
	/**
	 * Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getSetupIdList()
	 * @method fillSetupId()
	 * @method \string[] getNameList()
	 * @method \string[] getValueList()
	 * @method fillValue()
	 * @method \YandexPay\Pay\Trading\Setup\Model[] getSetupList()
	 * @method fillSetup()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\YandexPay\Pay\Trading\Settings\Model $object)
	 * @method bool has(\YandexPay\Pay\Trading\Settings\Model $object)
	 * @method bool hasByPrimary($primary)
	 * @method \YandexPay\Pay\Trading\Settings\Model getByPrimary($primary)
	 * @method \YandexPay\Pay\Trading\Settings\Model[] getAll()
	 * @method bool remove(\YandexPay\Pay\Trading\Settings\Model $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \YandexPay\Pay\Trading\Settings\Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \YandexPay\Pay\Trading\Settings\Model current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Repository_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \YandexPay\Pay\Trading\Settings\RepositoryTable */
		static public $dataClass = '\YandexPay\Pay\Trading\Settings\RepositoryTable';
	}
}
namespace YandexPay\Pay\Trading\Settings {
	/**
	 * @method static EO_Repository_Query query()
	 * @method static EO_Repository_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_Repository_Result getById($id)
	 * @method static EO_Repository_Result getList(array $parameters = array())
	 * @method static EO_Repository_Entity getEntity()
	 * @method static \YandexPay\Pay\Trading\Settings\Model createObject($setDefaultValues = true)
	 * @method static \YandexPay\Pay\Trading\Settings\Collection createCollection()
	 * @method static \YandexPay\Pay\Trading\Settings\Model wakeUpObject($row)
	 * @method static \YandexPay\Pay\Trading\Settings\Collection wakeUpCollection($rows)
	 */
	class RepositoryTable extends \Bitrix\Main\ORM\Data\DataManager {}
	/**
	 * @method EO_Repository_Result exec()
	 * @method \YandexPay\Pay\Trading\Settings\Model fetchObject()
	 * @method \YandexPay\Pay\Trading\Settings\Collection fetchCollection()
	 */
	class EO_Repository_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \YandexPay\Pay\Trading\Settings\Model fetchObject()
	 * @method \YandexPay\Pay\Trading\Settings\Collection fetchCollection()
	 */
	class EO_Repository_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \YandexPay\Pay\Trading\Settings\Model createObject($setDefaultValues = true)
	 * @method \YandexPay\Pay\Trading\Settings\Collection createCollection()
	 * @method \YandexPay\Pay\Trading\Settings\Model wakeUpObject($row)
	 * @method \YandexPay\Pay\Trading\Settings\Collection wakeUpCollection($rows)
	 */
	class EO_Repository_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:YandexPay\Pay\Injection\Setup\RepositoryTable */
namespace YandexPay\Pay\Injection\Setup {
	/**
	 * Model
	 * @see \YandexPay\Pay\Injection\Setup\RepositoryTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \YandexPay\Pay\Injection\Setup\Model setId(\int $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getTradingId()
	 * @method \YandexPay\Pay\Injection\Setup\Model setTradingId(\int $tradingId)
	 * @method bool hasTradingId()
	 * @method bool isTradingIdFilled()
	 * @method bool isTradingIdChanged()
	 * @method \int remindActualTradingId()
	 * @method \int requireTradingId()
	 * @method \YandexPay\Pay\Injection\Setup\Model resetTradingId()
	 * @method \YandexPay\Pay\Injection\Setup\Model unsetTradingId()
	 * @method \int fillTradingId()
	 * @method \string getBehavior()
	 * @method \YandexPay\Pay\Injection\Setup\Model setBehavior(\string $behavior)
	 * @method bool hasBehavior()
	 * @method bool isBehaviorFilled()
	 * @method bool isBehaviorChanged()
	 * @method \string remindActualBehavior()
	 * @method \string requireBehavior()
	 * @method \YandexPay\Pay\Injection\Setup\Model resetBehavior()
	 * @method \YandexPay\Pay\Injection\Setup\Model unsetBehavior()
	 * @method \string fillBehavior()
	 * @method array getSettings()
	 * @method \YandexPay\Pay\Injection\Setup\Model setSettings(array $settings)
	 * @method bool hasSettings()
	 * @method bool isSettingsFilled()
	 * @method bool isSettingsChanged()
	 * @method array remindActualSettings()
	 * @method array requireSettings()
	 * @method \YandexPay\Pay\Injection\Setup\Model resetSettings()
	 * @method \YandexPay\Pay\Injection\Setup\Model unsetSettings()
	 * @method array fillSettings()
	 * @method \YandexPay\Pay\Trading\Setup\Model getTrading()
	 * @method \YandexPay\Pay\Trading\Setup\Model remindActualTrading()
	 * @method \YandexPay\Pay\Trading\Setup\Model requireTrading()
	 * @method \YandexPay\Pay\Injection\Setup\Model setTrading(\YandexPay\Pay\Trading\Setup\Model $object)
	 * @method \YandexPay\Pay\Injection\Setup\Model resetTrading()
	 * @method \YandexPay\Pay\Injection\Setup\Model unsetTrading()
	 * @method bool hasTrading()
	 * @method bool isTradingFilled()
	 * @method bool isTradingChanged()
	 * @method \YandexPay\Pay\Trading\Setup\Model fillTrading()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \YandexPay\Pay\Injection\Setup\Model set($fieldName, $value)
	 * @method \YandexPay\Pay\Injection\Setup\Model reset($fieldName)
	 * @method \YandexPay\Pay\Injection\Setup\Model unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \YandexPay\Pay\Injection\Setup\Model wakeUp($data)
	 */
	class EO_Repository {
		/* @var \YandexPay\Pay\Injection\Setup\RepositoryTable */
		static public $dataClass = '\YandexPay\Pay\Injection\Setup\RepositoryTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace YandexPay\Pay\Injection\Setup {
	/**
	 * Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getTradingIdList()
	 * @method fillTradingId()
	 * @method \string[] getBehaviorList()
	 * @method fillBehavior()
	 * @method array[] getSettingsList()
	 * @method fillSettings()
	 * @method \YandexPay\Pay\Trading\Setup\Model[] getTradingList()
	 * @method fillTrading()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\YandexPay\Pay\Injection\Setup\Model $object)
	 * @method bool has(\YandexPay\Pay\Injection\Setup\Model $object)
	 * @method bool hasByPrimary($primary)
	 * @method \YandexPay\Pay\Injection\Setup\Model getByPrimary($primary)
	 * @method \YandexPay\Pay\Injection\Setup\Model[] getAll()
	 * @method bool remove(\YandexPay\Pay\Injection\Setup\Model $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \YandexPay\Pay\Injection\Setup\Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \YandexPay\Pay\Injection\Setup\Model current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Repository_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \YandexPay\Pay\Injection\Setup\RepositoryTable */
		static public $dataClass = '\YandexPay\Pay\Injection\Setup\RepositoryTable';
	}
}
namespace YandexPay\Pay\Injection\Setup {
	/**
	 * @method static EO_Repository_Query query()
	 * @method static EO_Repository_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_Repository_Result getById($id)
	 * @method static EO_Repository_Result getList(array $parameters = array())
	 * @method static EO_Repository_Entity getEntity()
	 * @method static \YandexPay\Pay\Injection\Setup\Model createObject($setDefaultValues = true)
	 * @method static \YandexPay\Pay\Injection\Setup\Collection createCollection()
	 * @method static \YandexPay\Pay\Injection\Setup\Model wakeUpObject($row)
	 * @method static \YandexPay\Pay\Injection\Setup\Collection wakeUpCollection($rows)
	 */
	class RepositoryTable extends \Bitrix\Main\ORM\Data\DataManager {}
	/**
	 * @method EO_Repository_Result exec()
	 * @method \YandexPay\Pay\Injection\Setup\Model fetchObject()
	 * @method \YandexPay\Pay\Injection\Setup\Collection fetchCollection()
	 */
	class EO_Repository_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \YandexPay\Pay\Injection\Setup\Model fetchObject()
	 * @method \YandexPay\Pay\Injection\Setup\Collection fetchCollection()
	 */
	class EO_Repository_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \YandexPay\Pay\Injection\Setup\Model createObject($setDefaultValues = true)
	 * @method \YandexPay\Pay\Injection\Setup\Collection createCollection()
	 * @method \YandexPay\Pay\Injection\Setup\Model wakeUpObject($row)
	 * @method \YandexPay\Pay\Injection\Setup\Collection wakeUpCollection($rows)
	 */
	class EO_Repository_Entity extends \Bitrix\Main\ORM\Entity {}
}