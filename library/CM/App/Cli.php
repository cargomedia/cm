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
		$this->_addComposerAutoload($namespace, $subpath . 'library/');
		$this->_getOutput()->writeln('Added `' . $namespace . '` namespace to composer.json');

		CM_Bootloader::getInstance()->reloadNamespacePaths();
		if (is_a(CM_Bootloader::getInstance(), 'CM_Booloader') || $this->_getInput()->confirm('Would you like to create new bootloader?')) {
			$this->_createBootloader($namespace);

		} elseif ($this->_getInput()->confirm('Would you like to include newly created namespace in current Bootloader')) {
			$this->_addBootloaderNamespace($namespace);
		}
	}

	private function _createBootloader($namespace) {
		$namespaces = CM_Bootloader::getInstance()->getNamespaces();
		$namespaces[] = $namespace;
		$bootloaderClassName = $namespace . '_Bootloader';
		$bootloader = $this->_generator->createClassFilePhp($bootloaderClassName, 'CM_Bootloader');
		$bootloader->addMethod('public', 'getNamespaces', array(), "return array('" . implode("', '", $namespaces) . "');");
		$this->_logCreate($bootloader);
		if (is_a(CM_Bootloader::getInstance(), 'CM_Booloader') || $this->_getInput()->confirm('Would you like to replace current Bootloader within entry points?')) {
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
	 * @param string $namespace
	 * @param string $path
	 * @throws CM_Exception_Invalid
	 */
	private function _addComposerAutoload($namespace, $path) {
		$composerFile = new CM_File(DIR_ROOT . 'composer.json');
		$composerOptions = json_decode($composerFile->read(), true);
		$composerOptions['autoload']['psr-0'][$namespace . '_'] = $path;
		$json = json_encode($composerOptions);
		$json = str_replace('\\/', '/', $json);
		$composerFile->write($json);
		CM_Util::exec('cd ' . escapeshellarg(DIR_ROOT) . ' && composer dump-autoload');
	}

	private function _addBootloaderNamespace($namespace) {
		$currentBootloaderClass = get_class(CM_Bootloader::getInstance());
		$reflection = new ReflectionClass($currentBootloaderClass);
		$file = new CM_File_Php($reflection->getFileName());
		$namespaces = CM_Bootloader::getInstance()->getNamespaces();
		$namespaces[] = $namespace;

		$file->replaceMethod('public', 'getNamespaces', null, 'return array(\'' . implode($namespaces, '\', \'') . '\');');
		$this->_getOutput()->writeln($currentBootloaderClass . ' has been changed.');
	}

	public static function getPackageName() {
		return 'app';
	}
}
