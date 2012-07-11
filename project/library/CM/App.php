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
	 * @param			  $directory
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

	public function generateClassesConfig() {
		//foreach ()
	}
}
