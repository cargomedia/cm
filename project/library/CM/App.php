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

	/**
	 * @return int
	 */
	public function getVersion() {
		return (int) CM_Option::getInstance()->get('app.version');
	}

	/**
	 * @param int $version
	 */
	public function setVersion($version) {
		$version = (int) $version;
		CM_Option::getInstance()->set('app.version', $version);
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
	 * @param              $directory
	 * @param Closure|null $callbackBefore fn($version)
	 * @param Closure|null $callbackAfter  fn($version)
	 * @return int Number of version bumps
	 */
	public function runUpdateScripts($directory, Closure $callbackBefore = null, Closure $callbackAfter = null) {
		CM_Cache::flush();
		CM_CacheLocal::flush();
		$version = $versionStart = $this->getVersion();
		while (true) {
			$updateScript = $directory . ($version + 1) . '.php';
			if (!file_exists($updateScript)) {
				break;
			}
			$version++;
			if ($callbackBefore) {
				$callbackBefore($version);
			}
			require $updateScript;
			$this->setVersion($version);
			if ($callbackAfter) {
				$callbackAfter($version);
			}
		}
		return ($version - $versionStart);
	}


	public function generateActionVerbsConfig() {
		$content = '$config->CM_Action_Abstract = array();';
		foreach ($this->getActionVerbs(true) as $constant => $declaration) {
			$content .= PHP_EOL;
			$content .= '$config->ActionVerbs[' . $declaration . '] = \'' . CM_Util::camelize($constant) . '\';';
		}
		return $content;
	}

	/**
	 *
	 * @param bool|null $returnDeclarations
	 * @throws CM_Exception_Invalid
	 * @return int[]
	 */
	public function getActionVerbs($returnDeclarations = null) {
		$actionVerbs = array();
		$actionDeclarations = array();
		$classNames = CM_Util::getClassChildren('CM_Action_Abstract');
		foreach ($classNames as $className) {
			$class = new ReflectionClass($className);
			$constants = $class->getConstants();
			unset($constants['TYPE']);
			foreach ($constants as $constant => $value) {
				if (array_key_exists($constant, $actionVerbs) && $actionVerbs[$constant] !== $value) {
					throw new CM_Exception_Invalid('Constant `' . $className . '::' . $constant . '` already set. Tried to set value to `' . $value . '` - previously set to `' . $actionVerbs[$constant] . '`.');
				}
				if (!array_key_exists($constant, $actionVerbs) && in_array($value, $actionVerbs)) {
					throw new CM_Exception_Invalid('Cannot set `' . $className . '::' . $constant . '` to `' . $value . '`. This value is already used for `' . $className . '::' . array_search($value, $actionVerbs) . '`.');
				}
				if (!array_key_exists($constant, $actionVerbs)) {
					$actionVerbs[$constant] = $value;
					$actionDeclarations[$constant] = $className . '::' . $constant;
				}
			}
		}
		if ($returnDeclarations) {
			return $actionDeclarations;
		} else {
			return $actionVerbs;
		}
	}

	/**
	 * @return string
	 */
	public function generateClassTypesConfig() {
		$content = '';
		$typeNamespaces = array(
			'CM_Site_Abstract',
			'CM_Action_Abstract',
			'CM_Model_Abstract',
			'CM_Model_ActionLimit_Abstract',
			'CM_Model_Entity_Abstract',
			'CM_Model_StreamChannel_Abstract',
			'CM_Mail',
			'CM_Paging_Log_Abstract',
			'CM_Paging_ContentList_Abstract',
		);
		foreach ($typeNamespaces as $typeNamespace) {
			$content .= join(PHP_EOL, $this->_generateClassTypesConfig($typeNamespace));
		}
		return $content;
	}

	/**
	 * @param string $typeNamespace
	 * @throws CM_Exception_Invalid
	 * @return string[]
	 */
	public function getClassTypes($typeNamespace) {
		$verifiedClasses = array();
		$highestTypeUsed = 0;
		foreach (CM_Util::getClassChildren($typeNamespace) as $className) {
			$reflectionClass = new ReflectionClass($className);
			if ($reflectionClass->hasConstant('TYPE')) {
				$type = $className::TYPE;
				if (in_array($type, $verifiedClasses)) {
					throw new CM_Exception_Invalid('Duplicate `TYPE` constant for `' . $className . '` and `' . $verifiedClasses[$type] . '`. Both equal `' . $type . '` (within `' . $typeNamespace . '` type namespace).');
				}
				$verifiedClasses[$className] = $type;
				$highestTypeUsed = max($highestTypeUsed, $type);
			} elseif (!$reflectionClass->isAbstract()) {
				throw new CM_Exception_Invalid('`' . $className . '` does not have `TYPE` constant defined');
			}
		}
		return $verifiedClasses;
	}

	/**
	 * @param string $typeNamespace
	 * @return string[]
	 */
	private function _generateClassTypesConfig($typeNamespace) {
		$verifiedClasses = $this->getClassTypes($typeNamespace);
		$declarations = array();
		foreach ($verifiedClasses as $className => $type) {
			$declarations[$type] = '$config->' . $typeNamespace . '->types[' . $className . '::TYPE] = \'' . $className . '\'; // #' . $type;
		}

		$lines = array();
		$lines[] = '';
		$lines[] = 'if (!isset($config->' . $typeNamespace . ')) {';
		$lines[] = "\t" . '$config->' . $typeNamespace . ' = new StdClass();';
		$lines[] = '}';
		$lines[] = '$config->' . $typeNamespace . '->types = array();';
		$lines = array_merge($lines, $declarations);
		$lines[] = '// Highest type used: #' . $highestTypeUsed;
		$lines[] = '';
		return $lines;
	}
}
