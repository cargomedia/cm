<?php

class CM_App_Cli extends CM_Cli_Runnable_Abstract {

	/** @var CM_App_Generator */
	private $_generator;

	public function __construct(CM_InputStream_Interface $input = null, CM_OutputStream_Interface $output = null) {
		$this->_generator = new CM_App_Generator();
		parent::__construct($input, $output);
	}

	/**
	 * @param string $className
	 * @throws CM_Exception_Invalid
	 */
	public function createView($className) {
		if (class_exists($className)) {
			throw new CM_Exception_Invalid('`' . $className . '` already exists');
		}
		$this->_logCreate($this->_generator->createClassFilePhp($className));
		$this->_logCreate($this->_generator->createClassFileJavascript($className));
		$this->_logCreate($this->_generator->createViewTemplate($className));
		$this->_logCreate($this->_generator->createViewStylesheets($className));
	}

	/**
	 * @param string $className
	 */
	public function createClass($className) {
		$this->_logCreate($this->_generator->createClassFilePhp($className));
	}

	/**
	 * @param string $namespace
	 */
	public function createNamespace($namespace) {
		$subpath = '';
		if (DIR_LIBRARY) {
			$subpath = DIR_LIBRARY . $namespace . '/';
		}
		$paths = array(
			DIR_ROOT . $subpath . 'library/' . $namespace,
			DIR_ROOT . $subpath . 'layout/default',
		);
		foreach ($paths as $path) {
			CM_Util::mkDir($path);
			$this->_logCreate($path);
		}
		$this->_addComposerOption('autoload.psr-0.' . $namespace . '_', $subpath . 'library/');
		$this->_getOutput()->writeln('Added `' . $namespace . '` namespace to composer.json');

		CM_Bootloader::getInstance()->reloadNamespacePaths();
		if ($this->_getInput()->confirm('Would you like to create new bootloader?')) {
			$this->_createBootloader($namespace);

		} elseif ($this->_getInput()->confirm('Would you like to include newly created namespace in current Bootloader')) {
			// TODO: Change bootloader
			$currentBootloaderClass = get_class(CM_Bootloader::getInstance());
			$this->_getOutput()->writeln($currentBootloaderClass . ' has been changed.');
		}
	}

	private function _createBootloader($namespace) {
		$namespaces = CM_Bootloader::getInstance()->getNamespaces();
		$namespaces[] = $namespace;
		$bootloaderClassName = $namespace . '_Bootloader';
		$bootloader = $this->_generator->createClassFilePhp($bootloaderClassName, 'CM_Bootloader');
		$bootloader->addMethod('public', 'getNamespaces', array(), "return array('" . implode("', '", $namespaces) . "');");
		$this->_logCreate($bootloader);
		if ($this->_getInput()->confirm('Would you like to replace current Bootloader within entry points?')) {
			$this->_logCreate($this->_generator->createHttpEntryPoint($bootloaderClassName));
			$this->_logCreate($this->_generator->createScriptEntryPoint($bootloaderClassName));
		}
	}

	/**
	 * @param string|CM_File $created
	 */
	private function _logCreate($created) {
		if ($created instanceof CM_File) {
			$created = $created->getPath();
		}
		$this->_getOutput()->writeln('Created `' . $created . '`');
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @throws CM_Exception_Invalid
	 */
	private function _addComposerOption($name, $value) {
		$keys = explode('.', $name);
		$composerFile = new CM_File(DIR_ROOT . 'composer.json');
		$composerOptions = json_decode($composerFile->read());
		$node = $composerOptions;
		foreach ($keys as $key) {
			if (!isset($node->$key)) {
				$node->$key = new stdClass();
			} elseif (!is_object($node->$key)) {
				throw new CM_Exception_Invalid('Key `' . $key . '` is not empty or object type');
			}
			$node =& $node->$key;
		}
		$node = $value;
		$json = json_encode($composerOptions);
		$json = str_replace('\\/', '/', $json);
		$composerFile->write($json);
		CM_Util::exec('cd ' . escapeshellarg(DIR_ROOT) . ' && composer dump-autoload');
	}

	public static function getPackageName() {
		return 'app';
	}
}
