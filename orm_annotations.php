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
	 * @method \YandexPay\Pay\Trading\Setup\Model setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getSiteId()
	 * @method \YandexPay\Pay\Trading\Setup\Model setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \YandexPay\Pay\Trading\Setup\Model resetSiteId()
	 * @method \YandexPay\Pay\Trading\Setup\Model unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \string getPersonTypeId()
	 * @method \YandexPay\Pay\Trading\Setup\Model setPersonTypeId(\string|\Bitrix\Main\DB\SqlExpression $personTypeId)
	 * @method bool hasPersonTypeId()
	 * @method bool isPersonTypeIdFilled()
	 * @method bool isPersonTypeIdChanged()
	 * @method \string remindActualPersonTypeId()
	 * @method \string requirePersonTypeId()
	 * @method \YandexPay\Pay\Trading\Setup\Model resetPersonTypeId()
	 * @method \YandexPay\Pay\Trading\Setup\Model unsetPersonTypeId()
	 * @method \string fillPersonTypeId()
	 * @method \boolean getActive()
	 * @method \YandexPay\Pay\Trading\Setup\Model setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \YandexPay\Pay\Trading\Setup\Model resetActive()
	 * @method \YandexPay\Pay\Trading\Setup\Model unsetActive()
	 * @method \boolean fillActive()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
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
	 * @method \string[] fillSiteId()
	 * @method \string[] getPersonTypeIdList()
	 * @method \string[] fillPersonTypeId()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
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
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Repository_Result exec()
	 * @method \YandexPay\Pay\Trading\Setup\Model fetchObject()
	 * @method \YandexPay\Pay\Trading\Setup\Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
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