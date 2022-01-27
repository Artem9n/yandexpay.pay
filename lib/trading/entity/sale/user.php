<?php

namespace YandexPay\Pay\Trading\Entity\Sale;

use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;
use YandexPay\Pay\Utils\UserField;

class User extends EntityReference\User
{
	use Concerns\HasMessage;

	/** @var Environment */
	protected $environment;
	protected $id;

	protected static function includeMessages() : void
	{
		Main\Localization\Loc::loadMessages(__FILE__);
	}

	public function __construct(Environment $environment, $data)
	{
		parent::__construct($environment, $data);
	}

	public function getId() : ?int
	{
		if ($this->id === null)
		{
			$this->id = $this->searchUser();
		}

		return $this->id;
	}

	protected function searchUser() : ?int
	{
		$filters = $this->getSearchFilters();
		$result = null;

		foreach ($filters as $filter)
		{
			$query = Main\UserTable::getList([
				'filter' => $filter,
				'select' => [ 'ID' ],
				'limit' => 1,
			]);

			if ($row = $query->fetch())
			{
				$result = (int)$row['ID'];
				break;
			}
		}

		return $result;
	}

	protected function getSearchFilters() : array
	{
		$filters = $this->fillSearchFilters();
		return $this->sortSearchFiltersByPriority($filters);
	}

	protected function fillSearchFilters() : array
	{
		$filters = [];
		$xmlId = (string)$this->getXmlId();
		$email = isset($this->data['EMAIL']) ? trim($this->data['EMAIL']) : '';
		$phone = isset($this->data['PHONE']) ? trim($this->data['PHONE']) : '';

		if ($xmlId !== '')
		{
			$filters['XML_ID'] = [ '=XML_ID' => $xmlId ];
		}

		if ($email !== '')
		{
			$filters['EMAIL'] = [ '=EMAIL' => $email ];
		}

		if ($phone !== '')
		{
			// phone auth

			if ($this->hasPhoneRegistration() && $this->isPhoneValid($phone))
			{
				$filters['PHONE'] = [ '=PHONE_AUTH.PHONE_NUMBER' => $this->normalizePhoneNumber($phone) ];
			}

			// user field

			$phoneField = $this->getPhoneFieldName();

			$filters[$phoneField] = [ '=' . $phoneField => $phone ];
		}

		return $filters;
	}

	protected function sortSearchFiltersByPriority($filters) : array
	{
		$priority = [
			'XML_ID' => 10,
			'EMAIL' => $this->isEmailRequired() + $this->isEmailUnique(),
			'PHONE' => $this->isPhoneRequired() + $this->isPhoneUnique(),
		];

		uksort($filters, static function($aKey, $bKey) use ($priority) {
			$aPriority = $priority[$aKey] ?? -1;
			$bPriority = $priority[$bKey] ?? -1;

			if ($aPriority === $bPriority) { return 0; }

			return $aPriority > $bPriority ? -1 : 1;
		});

		return $filters;
	}

	public function attachGroup($groupId)
	{
		$groupId = (int)$groupId;
		$userId = $this->getId();
		$result = new Main\Result();

		if ($userId === null)
		{
			$error = new Main\Error(self::getMessage('NEED_INSTALL_BEFORE_ATTACH_GROUP'));
			$result->addError($error);
		}
		else
		{
			$existGroups = [];
			$existGroupIds = [];
			$queryExistGroups = \CUser::GetUserGroupList($userId);

			while ($existGroup = $queryExistGroups->Fetch())
			{
				$existGroupIds[] = (int)$existGroup['GROUP_ID'];
				$existGroups[] = $existGroup;
			}

			if (!in_array($groupId, $existGroupIds, true))
			{
				$groups = $existGroups;
				$groups[] = [ 'GROUP_ID' => $groupId ];

				\CUser::SetUserGroup($userId, $groups); // no result, always success
			}
		}

		return $result;
	}

	public function install(array $data = []) : Main\Entity\AddResult
	{
		$userData = $data + $this->data;
		$registerData = $this->convertUserData($userData);
		$registerData += $this->getDefaultData();
		$registerData += $this->getRequiredData();

		[$email, $registerData] = $this->extractEmailField($registerData);
		[$payer, $registerData] = $this->extractPayerField($registerData);
		[$siteId, $registerData] = $this->extractSiteIdField($registerData);

		$addResult = $this->createUser($email, $payer, $siteId, $registerData);

		if ($addResult->isSuccess())
		{
			$this->id = $addResult->getId();
		}

		return $addResult;
	}

	protected function getRequiredData()
	{
		$defaultValue = new UserField\DefaultValue('USER');

		return $defaultValue->getValues('MANDATORY');
	}

	public function migrate($code)
	{
		throw new Main\NotSupportedException();
	}

	public function update(array $data)
	{
		$userId = $this->getId();
		$result = new Main\Entity\UpdateResult();

		if ($userId === null)
		{
			$error = new Main\Error('cant update not installed user');
			$result->addError($error);
		}
		else if (!empty($data))
		{
			$updateProvider = new \CUser();
			$updateResult = $updateProvider->Update($userId, $data);

			if ($updateResult === false)
			{
				$error = new Main\Error($updateProvider->LAST_ERROR);
				$result->addError($error);
			}
		}

		return $result;
	}

	protected function createUser($email, $payer, $siteId, $data) : Main\Entity\AddResult
	{
		$result = new Main\Entity\AddResult();
		$errors = [];
		$email = $this->resolveEmail($email);
		$data = $this->fillUserDataPhone($data);

		$userId = \CSaleUser::DoAutoRegisterUser(
			$email,
			$payer,
			$siteId,
			$errors,
			$data
		);

		if ($userId > 0)
		{
			$result->setId($userId);
		}
		else if (!empty($errors))
		{
			foreach ($errors as $errorData)
			{
				$error = new Main\Error($errorData['TEXT']);
				$result->addError($error);
			}
		}
		else
		{
			$message = self::getMessage('FAIL_AUTO_REGISTER_USER');
			$error = new Main\Error($message);

			$result->addError($error);
		}

		return $result;
	}

	protected function fillUserDataPhone($data)
	{
		$isExternal = isset($data['EXTERNAL_AUTH_ID']) && trim($data['EXTERNAL_AUTH_ID']) !== '';

		if (!$isExternal && $this->hasPhoneRegistration())
		{
			$hasPhoneNumberField = isset($data['PHONE_NUMBER']);
			$originalNumber = $hasPhoneNumberField ? $data['PHONE_NUMBER'] : null;
			$phoneNumber = $this->resolvePhone($originalNumber);

			if ($phoneNumber !== null)
			{
				$data['PHONE_NUMBER'] = $phoneNumber;

				if ($hasPhoneNumberField && $originalNumber !== $phoneNumber)
				{
					$phoneField = $this->getPhoneFieldName();

					if (empty($data[$phoneField]))
					{
						$data[$phoneField] = $originalNumber;
					}
				}
			}
			else if ($hasPhoneNumberField)
			{
				$phoneField = $this->getPhoneFieldName();

				if (empty($data[$phoneField]))
				{
					$data[$phoneField] = $originalNumber;
				}

				unset($data['PHONE_NUMBER']);
			}
		}

		return $data;
	}

	protected function extractEmailField($data)
	{
		return $this->extractSingleField($data, [ 'EMAIL' ]);
	}

	protected function extractSiteIdField($data)
	{
		return $this->extractSingleField($data, [ 'LID', 'SITE_ID' ]);
	}

	protected function extractPayerField($data)
	{
		return $this->extractMultipleField($data, [
			'NAME' => ['FIRST_NAME', 'NAME'],
			'LAST_NAME' => ['LAST_NAME'],
			'SECOND_NAME' => ['MIDDLE_NAME', 'SECOND_NAME'],
		]);
	}

	protected function extractSingleField($data, $keys)
	{
		$keys = (array)$keys;
		$value = null;

		foreach ($keys as $key)
		{
			if (isset($data[$key]))
			{
				$value = $data[$key];
				unset($data[$key]);
				break;
			}
		}

		return [$value, $data];
	}

	protected function extractMultipleField($data, $keys)
	{
		$keys = (array)$keys;
		$field = null;

		foreach ($keys as $target => $variants)
		{
			if (is_numeric($target) && is_string($variants))
			{
				$target = $variants;
				$variants = [ $variants ];
			}

			foreach ($variants as $variant)
			{
				if (!isset($data[$variant])) { continue; }

				if ($field === null)
				{
					$field = [];
				}

				$field[$target] = $data[$variant];
				unset($data[$variant]);
				break;
			}
		}

		return [$field, $data];
	}

	protected function getDataLogin(array $data)
	{
		if (!empty($data['LOGIN']))
		{
			$result = $this->sanitizeLogin($data['LOGIN']);
		}
		else if (!empty($data['EMAIL']))
		{
			$result = $data['EMAIL'];
			$delimiterPosition = mb_strpos($result, '@');

			if ($delimiterPosition !== false)
			{
				$result = mb_substr($result, 0, $delimiterPosition);
			}

			$result = $this->sanitizeLogin($result);
		}
		else
		{
			$result = 'buyer';
		}

		return $result;
	}

	protected function sanitizeLogin($login)
	{
		$loginLength = mb_strlen($login);
		$bottomLengthLimit = 3;
		$topLengthLimit = 47;

		if ($loginLength > $topLengthLimit)
		{
			$loginLength = $topLengthLimit;
			$login = mb_substr($login, 0, $topLengthLimit);
		}

		if ($bottomLengthLimit > $loginLength)
		{
			$loginLength = $bottomLengthLimit;
			$login = str_repeat('_', $bottomLengthLimit - $loginLength);
		}

		return $login;
	}

	protected function existsLogin($login)
	{
		$filter = [ '=LOGIN' => $login ];

		if ($this->id !== null)
		{
			$filter['!=ID'] = $this->id;
		}

		$query = Main\UserTable::getList([
			'filter' => $filter,
			'select' => [ 'ID' ],
			'limit' => 1,
		]);

		return (bool)$query->fetch();
	}

	protected function makeUniqueLogin($login, $randomizeAtStart = false)
	{
		$nextLogin = $randomizeAtStart ? $this->randomizeLogin($login) : $login;
		$result = $login;

		do
		{
			$foundDuplicate = $this->existsLogin($nextLogin);

			if ($foundDuplicate)
			{
				$nextLogin = $this->randomizeLogin($login);
			}
			else
			{
				$result = $nextLogin;
			}
		}
		while ($foundDuplicate);

		return $result;
	}

	protected function randomizeLogin($login)
	{
		return $login . '_' . randString(5);
	}

	protected function isEmailUnique()
	{
		return (Main\Config\Option::get('main', 'new_user_email_uniq_check', 'N') === 'Y');
	}

	protected function isEmailRequired()
	{
		return (Main\Config\Option::get('main', 'new_user_email_required', 'Y') !== 'N');
	}

	protected function isEmailValid($email)
	{
		$email = trim($email);

		return ($email !== '' && check_email($email, true));
	}

	protected function resolveEmail($email)
	{
		$email = trim($email);
		$result = null;

		if ($this->isEmailRequired())
		{
			if (!$this->isEmailValid($email))
			{
				$email = $this->createRandomEmail();
				$result = $this->makeUniqueEmail($email, true);
			}
			else
			{
				$result = $this->makeUniqueEmail($email);
			}
		}
		else if (
			$this->isEmailValid($email)
			&& (!$this->isEmailUnique() || !$this->existsEmail($email))
		)
		{
			$result = $email;
		}

		return $result;
	}

	protected function createRandomEmail()
	{
		return 'noemail@' . $this->getSiteHost();
	}

	protected function getSiteHost()
	{
		if (defined('SITE_SERVER_NAME') && trim(SITE_SERVER_NAME) !== '')
		{
			$result = SITE_SERVER_NAME;
		}
		else
		{
			$result = trim(Main\Context::getCurrent()->getRequest()->getHttpHost());

			if ($result === '')
			{
				$result = Main\Config\Option::get('main', 'server_name');
			}
		}

		return $result;
	}

	protected function existsEmail($email)
	{
		$filter = [ '=EMAIL' => $email ];

		if ($this->id !== null)
		{
			$filter['!=ID'] = $this->id;
		}

		$query = Main\UserTable::getList([
			'filter' => $filter,
			'select' => [ 'ID' ],
			'limit' => 1,
		]);

		return (bool)$query->fetch();
	}

	protected function makeUniqueEmail($email, $randomizeAtStart = false)
	{
		$nextEmail = $randomizeAtStart ? $this->randomizeEmail($email) : $email;
		$result = $email;

		do
		{
			$foundDuplicate = $this->existsEmail($nextEmail);

			if ($foundDuplicate)
			{
				$nextEmail = $this->randomizeEmail($email);
			}
			else
			{
				$result = $nextEmail;
			}
		}
		while ($foundDuplicate);

		return $result;
	}

	protected function randomizeEmail($email)
	{
		$delimiterPosition = mb_strpos($email, '@');
		$randomizer = '_' . randString(5);

		if ($delimiterPosition !== false)
		{
			$result =
				mb_substr($email, 0, $delimiterPosition)
				. $randomizer
				. mb_substr($email, $delimiterPosition);
		}
		else
		{
			$result = $email . $randomizer;
		}

		return $result;
	}

	protected function hasPhoneRegistration()
	{
		return class_exists(Main\UserPhoneAuthTable::class);
	}

	protected function isPhoneValid($phone)
	{
		$phone = trim($phone);

		return (
			$phone !== ''
			&& Main\UserPhoneAuthTable::validatePhoneNumber($phone) === true
		);
	}

	protected function normalizePhoneNumber($phone)
	{
		return Main\UserPhoneAuthTable::normalizePhoneNumber($phone);
	}

	protected function isPhoneUnique()
	{
		return true;
	}

	protected function isPhoneRequired()
	{
		return (Main\Config\Option::get('main', 'new_user_phone_required', 'N') === 'Y');
	}

	protected function resolvePhone($phone)
	{
		$phone = trim($phone);
		$result = null;

		if ($this->isPhoneRequired())
		{
			if (!$this->isPhoneValid($phone))
			{
				$phone = $this->createRandomPhone();
				$result = $this->makeUniquePhone($phone, true);
			}
			else
			{
				$result = $this->makeUniquePhone($phone);
			}
		}
		else if (
			$this->isPhoneValid($phone)
			&& (!$this->isPhoneUnique() || !$this->existsPhone($phone))
		)
		{
			$result = $phone;
		}

		return $result;
	}

	protected function createRandomPhone()
	{
		return '+74950000000';
	}

	protected function existsPhone($phone)
	{
		$normalizedPhone = (string)$this->normalizePhoneNumber($phone);
		$result = false;

		if ($normalizedPhone !== '')
		{
			$filter = [ '=PHONE_NUMBER' => $normalizedPhone ];

			if ($this->id !== null)
			{
				$filter['!=USER_ID'] = $this->id;
			}

			$query = Main\UserPhoneAuthTable::getList([
				'filter' => $filter,
				'select' => [ 'USER_ID' ],
				'limit' => 1,
			]);

			$result = (bool)$query->fetch();
		}

		return $result;
	}

	protected function makeUniquePhone($phone, $randomizeAtStart = false)
	{
		$nextPhone = $randomizeAtStart ? $this->randomizePhone($phone) : $phone;
		$result = $phone;

		do
		{
			$foundDuplicate = $this->existsPhone($nextPhone);

			if ($foundDuplicate)
			{
				$nextPhone = $this->randomizePhone($nextPhone);
			}
			else
			{
				$result = $nextPhone;
			}
		}
		while ($foundDuplicate);

		return $result;
	}

	protected function randomizePhone($phone)
	{
		$replacesLimit = 4;
		$replacesCount = 0;
		$phoneLength = mb_strlen($phone);

		for ($symbolIndex = $phoneLength - 1; $symbolIndex >= 0; $symbolIndex--)
		{
			$symbol = mb_substr($phone, $symbolIndex, 1);

			if (preg_match('/\d/', $symbol))
			{
				$replacesCount++;
				$randomDigit = random_int(0, 9);
				$phone =
					mb_substr($phone, 0, $symbolIndex)
					. $randomDigit
					. mb_substr($phone, $symbolIndex + 1);

				if ($replacesCount >= $replacesLimit) { break; }
			}
		}

		return $phone;
	}

	protected function convertUserData($data)
	{
		if (isset($data['ID']))
		{
			unset($data['ID']);
		}

		if (isset($data['PHONE']))
		{
			/*$phoneFormatted = Market\Data\Phone::format($data['PHONE']);
			$phoneInternational = Market\Data\Phone::format($data['PHONE'], Market\Data\Phone::FORMAT_INTERNATIONAL_NUMERIC);*/

			// user field

			$phoneField = $this->getPhoneFieldName();

			$data[$phoneField] = $data['PHONE'];

			// phone auth

			if ($this->hasPhoneRegistration() && $this->isPhoneValid($data['PHONE']))
			{
				$data['PHONE_NUMBER'] = $data['PHONE'];
			}

			unset($data['PHONE']);
		}

		return $data;
	}

	protected function getDefaultData() : array
	{
		return [
			'ACTIVE' => 'Y',
			'XML_ID' => $this->getXmlId(),
		];
	}

	protected function getXmlId() : ?string
	{
		$result = null;

		if (isset($this->data['ID']))
		{
			$result = 'yapay_' . $this->data['ID'];
		}

		return $result;
	}

	protected function getPhoneFieldName()
	{
		return 'PERSONAL_MOBILE';//(string)Market\Config::getOption('user_phone_field', 'PERSONAL_MOBILE');
	}
}