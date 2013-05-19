<?php

class CM_App {

	/**
	 * @var CM_App
	 */
	private static $_instance;

	/**
	 * @return CM_App
	 */
	public static function getInstance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function setupFilesystem() {
		CM_Util::mkDir(DIR_TMP);
		CM_Util::rmDirContents(DIR_TMP);
		CM_Util::mkDir(DIR_TMP_SMARTY);
		CM_Util::mkDir(DIR_TMP_CACHE);
		CM_Util::mkDir(DIR_TMP_SMARTY);

		CM_Util::mkDir(DIR_DATA);
		CM_Util::mkDir(DIR_DATA_SVM);
		CM_Util::mkDir(DIR_DATA_LOCKS);
		CM_Util::mkDir(DIR_DATA_LOG);

		CM_Util::mkDir(DIR_USERFILES);
	}

	/**
	 * @param boolean|null $forceReload
	 */
	public function setupDatabase($forceReload = null) {
		$configDb = CM_Config::get()->CM_Db_Db;
		$client = new CM_Db_Client($configDb->server['host'], $configDb->server['port'], $configDb->username, $configDb->password);

		if ($forceReload) {
			$client->createStatement('DROP DATABASE IF EXISTS ' . $client->quoteIdentifier($configDb->db))->execute();
		}

		$databaseExists = (bool) $client->createStatement('SHOW DATABASES LIKE ?')->execute(array($configDb->db))->fetch();
		if (!$databaseExists) {
			$client->createStatement('CREATE DATABASE ' . $client->quoteIdentifier($configDb->db))->execute();
			foreach (CM_Util::getResourceFiles('db/structure.sql') as $dump) {
				CM_Db_Db::runDump($configDb->db, $dump);
			}
		}
	}

	/**
	 * @param string|null $namespace
	 * @return int
	 */
	public function getVersion($namespace = null) {
		$namespace = (string) $namespace;
		if ($namespace) {
			$namespace = '.' . $namespace;
		}
		return (int) CM_Option::getInstance()->get('app.version' . $namespace);
	}

	/**
	 * @param int         $version
	 * @param string|null $namespace
	 */
	public function setVersion($version, $namespace = null) {
		$version = (int) $version;
		$namespace = (string) $namespace;
		if ($namespace) {
			$namespace = '.' . $namespace;
		}
		CM_Option::getInstance()->set('app.version' . $namespace, $version);
	}

	/**
	 * @return int
	 */
	public function getReleaseStamp() {
		return (int) CM_Option::getInstance()->get('app.releaseStamp');
	}

	/**
	 * @param int|null $releaseStamp
	 */
	public function setReleaseStamp($releaseStamp = null) {
		if (null === $releaseStamp) {
			$releaseStamp = time();
		}
		$releaseStamp = (int) $releaseStamp;
		CM_Option::getInstance()->set('app.releaseStamp', $releaseStamp);
	}

	/**
	 * @param Closure|null $callbackBefore fn($version)
	 * @param Closure|null $callbackAfter  fn($version)
	 * @return int Number of version bumps
	 */
	public function runUpdateScripts(Closure $callbackBefore = null, Closure $callbackAfter = null) {
		CM_Cache::flush();
		CM_CacheLocal::flush();
		$versionBumps = 0;
		foreach ($this->_getUpdateScriptPaths() as $namespace => $path) {
			$version = $versionStart = $this->getVersion($namespace);
			while (true) {
				$version++;
				if (!$this->runUpdateScript($namespace, $version, $callbackBefore, $callbackAfter)) {
					$version--;
					break;
				}
				$this->setVersion($version, $namespace);
			}
			$versionBumps += ($version - $versionStart);
		}
		return $versionBumps;
	}

	/**
	 * @param string       $namespace
	 * @param int          $version
	 * @param Closure|null $callbackBefore
	 * @param Closure|null $callbackAfter
	 * @return int
	 */
	public function runUpdateScript($namespace, $version, Closure $callbackBefore = null, Closure $callbackAfter = null) {
		try {
			$updateScript = $this->_getUpdateScriptPath($version, $namespace);
		} catch (CM_Exception_Invalid $e) {
			return 0;
		}
		if ($callbackBefore) {
			$callbackBefore($version);
		}
		require $updateScript;
		if ($callbackAfter) {
			$callbackAfter($version);
		}
		return 1;
	}

	public function generateConfigActionVerbs() {
		$content = 'if (!isset($config->CM_Action_Abstract)) {' . PHP_EOL;
		$content .= '	$config->CM_Action_Abstract = new StdClass();' . PHP_EOL;
		$content .= '}' . PHP_EOL;
		$content .= '$config->CM_Action_Abstract->verbs = array();';
		foreach ($this->getActionVerbs() as $actionVerb) {
			$content .= PHP_EOL;
			$content .= '$config->CM_Action_Abstract->verbs[' . $actionVerb['className'] . '::' . $actionVerb['name'] . '] = \'' .
					CM_Util::camelize($actionVerb['name']) . '\';';
		}
		return $content;
	}

	/**
	 * @return string
	 */
	public function generateConfigClassTypes() {
		$content = '';
		$typeNamespaces = array('CM_Site_Abstract', 'CM_Action_Abstract', 'CM_Model_Abstract', 'CM_Model_ActionLimit_Abstract',
			'CM_Model_Entity_Abstract', 'CM_Model_StreamChannel_Abstract', 'CM_Mail', 'CM_Paging_Log_Abstract', 'CM_Paging_ContentList_Abstract',);
		foreach ($typeNamespaces as $typeNamespace) {
			$content .= join(PHP_EOL, $this->_generateClassTypesConfig($typeNamespace));
		}
		return $content;
	}

	/**
	 *
	 * @throws CM_Exception_Invalid
	 * @return array
	 */
	public function getActionVerbs() {
		$actionVerbs = array();
		$actionVerbsValues = array();
		foreach (CM_Action_Abstract::getClassChildren(true) as $className) {
			$class = new ReflectionClass($className);
			$constants = $class->getConstants();
			unset($constants['TYPE']);
			foreach ($constants as $constant => $value) {
				if (array_key_exists($constant, $actionVerbsValues) && $actionVerbsValues[$constant] !== $value) {
					throw new CM_Exception_Invalid(
						'Constant `' . $className . '::' . $constant . '` already set. Tried to set value to `' . $value . '` - previously set to `' .
								$actionVerbsValues[$constant] . '`.');
				}
				if (!array_key_exists($constant, $actionVerbsValues) && in_array($value, $actionVerbsValues)) {
					throw new CM_Exception_Invalid(
						'Cannot set `' . $className . '::' . $constant . '` to `' . $value . '`. This value is already used for `' . $className .
								'::' . array_search($value, $actionVerbsValues) . '`.');
				}
				if (!array_key_exists($constant, $actionVerbsValues)) {
					$actionVerbsValues[$constant] = $value;
					$actionVerbs[] = array('name' => $constant, 'value' => $value, 'className' => $className,);
				}
			}
		}
		return $actionVerbs;
	}

	/**
	 * @param string $className
	 * @throws CM_Exception_Invalid
	 * @return string[]
	 */
	public function getClassTypes($className) {
		$classTypes = array();
		/** @var $className CM_Class_Abstract */
		foreach ($className::getClassChildren() as $className) {
			$reflectionClass = new ReflectionClass($className);
			if ($reflectionClass->hasConstant('TYPE')) {
				$type = $className::TYPE;
				if ($classNameDuplicate = array_search($type, $classTypes)) {
					throw new CM_Exception_Invalid(
						'Duplicate `TYPE` constant for `' . $className . '` and `' . $classNameDuplicate . '`. Both equal `' . $type . '` (within `' .
								$className . '` type namespace).');
				}
				$classTypes[$className] = $type;
			} elseif (!$reflectionClass->isAbstract()) {
				throw new CM_Exception_Invalid('`' . $className . '` does not have `TYPE` constant defined');
			}
		}
		return $classTypes;
	}

	/**
	 * @return string[]
	 */
	private function _getUpdateScriptPaths() {
		$paths = array();
		foreach (CM_Bootloader::getInstance()->getNamespaces() as $namespace) {
			$paths[$namespace] = CM_Util::getNamespacePath($namespace) . 'resources/db/update/';
		}

		$rootPath = DIR_ROOT . 'resources/db/update/';
		if (!in_array($rootPath, $paths)) {
			$paths[null] = $rootPath;
		}

		return $paths;
	}

	/**
	 * @param int         $version
	 * @param string|null $namespace
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	private function _getUpdateScriptPath($version, $namespace = null) {
		$path = DIR_ROOT;
		if ($namespace) {
			$path = CM_Util::getNamespacePath($namespace);
		}
		$updateScript = $path . 'resources/db/update/' . $version . '.php';
		if (!CM_File::exists($updateScript)) {
			throw new CM_Exception_Invalid('Update script `' . $version . '` does not exist for `' . $namespace . '` namespace.');
		}
		return $updateScript;
	}

	/**
	 * @param string $className
	 * @return string[]
	 */
	private function _generateClassTypesConfig($className) {
		$declarations = array();
		$highestTypeUsed = 0;
		foreach ($this->getClassTypes($className) as $childClassName => $type) {
			$declarations[$type] = '$config->' . $className . '->types[' . $childClassName . '::TYPE] = \'' . $childClassName . '\'; // #' . $type;
			$highestTypeUsed = max($highestTypeUsed, $type);
		}

		$lines = array();
		$lines[] = '';
		$lines[] = 'if (!isset($config->' . $className . ')) {';
		$lines[] = "\t" . '$config->' . $className . ' = new StdClass();';
		$lines[] = '}';
		$lines[] = '$config->' . $className . '->types = array();';
		$lines = array_merge($lines, $declarations);
		$lines[] = '// Highest type used: #' . $highestTypeUsed;
		$lines[] = '';
		return $lines;
	}
}
