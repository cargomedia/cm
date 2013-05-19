<?php

class CM_Generator_Cli extends CM_Cli_Runnable_Abstract {

	/** @var CM_CodeGenerator_Php */
	protected $_generatorPhp;

	/** @var CM_CodeGenerator_Javascript */
	protected $_generatorJavascript;

	public function __construct(CM_InputStream_Interface $input = null, CM_OutputStream_Interface $output = null) {
		parent::__construct($input, $output);
		$this->_generatorPhp = new CM_CodeGenerator_Php();
		$this->_generatorJavascript = new CM_CodeGenerator_Javascript();
	}

	/**
	 * @param string $className
	 * @throws CM_Exception_Invalid
	 */
	public function createView($className) {
		if (class_exists($className)) {
			throw new CM_Exception_Invalid('`' . $className . '` already exists');
		}
		$phpClassFile = $this->_generatorPhp->createClassFile($className);
		$this->_logFileCreation($phpClassFile);
		$jsClassFile = $this->_generatorJavascript->createClassFile($className);
		$this->_logFileCreation($jsClassFile);
		$this->_generateViewLayout($className);
	}

	/**
	 * @param string $className
	 * @throws CM_Exception_Invalid
	 */
	public function createClass($className) {
		if (class_exists($className) && !$this->_getInput()->confirm('Class `' . $className . '` already exists. Replace?')) {
			return;
		}
		$file = $this->_generatorPhp->createClassFile($className);
		$this->_logFileCreation($file);
	}

	/**
	 * @param string $namespace
	 */
	public function createNamespace($namespace) {
		$this->_createNamespaceDirectories($namespace);
		CM_Bootloader::getInstance()->reloadNamespacePaths();
		$this->_generatorPhp->createClassFile($namespace . '_Site');

		$bootloaderClass = $this->_generatorPhp->createClass($namespace . '_Bootloader');
		$namespaces = array_merge(CM_Bootloader::getInstance()->getNamespaces(), array($namespace));
		$bootloaderClass->addMethod(new CG_Method('getNamespaces', "return array('" . implode("', '", $namespaces) . "');"));
		$this->_generatorPhp->createClassFileFromClass($bootloaderClass);
	}

	public function createJavascriptFiles() {
		$viewClasses = CM_View_Abstract::getClasses(CM_Bootloader::getInstance()->getNamespaces(), CM_View_Abstract::CONTEXT_JAVASCRIPT);
		foreach ($viewClasses as $path => $className) {
			$jsPath = preg_replace('/\.php$/', '.js', $path);
			if (!CM_File::exists($jsPath)) {
				$jsFile = CM_File_Javascript::createLibraryClass($className);
				$this->_getOutput()->writeln('Created `' . $jsFile->getPath() . '`');
			}
		}
	}

	/**
	 * @param string $namespace
	 */
	private function _createNamespaceDirectories($namespace) {
		$paths = array();
		$paths[] = DIR_ROOT . DIR_LIBRARY . $namespace . '/library/' . $namespace;
		$paths[] = DIR_ROOT . DIR_LIBRARY . $namespace . '/layout/default';
		foreach ($paths as $path) {
			CM_Util::mkDir($path);
			$this->_getOutput()->writeln('Created `'  . $path . '`');
		}
	}

	/**
	 * @param string $className
	 */
	private function _generateViewLayout($className) {
		$parts = explode('_', $className);
		$namespace = array_shift($parts);
		$viewType = array_shift($parts);
		$pathRelative = implode('_', $parts);
		$layoutPath = CM_Util::getNamespacePath($namespace) . 'layout/default/' . $viewType . '/' . $pathRelative . '/';
		CM_Util::mkDir($layoutPath);
		$file = CM_File::create($layoutPath . 'default.tpl');
		$this->_getOutput()->writeln('Created `' . $file->getPath() . '`');
		$file = CM_File::create($layoutPath . 'default.less');
		$this->_getOutput()->writeln('Created `' . $file->getPath() . '`');
	}

	/**
	 * @param CM_File $file
	 */
	private function _logFileCreation(CM_File $file) {
		$this->_getOutput()->writeln('Created `' . $file->getPath() . '`');
	}

	public static function getPackageName() {
		return 'generator';
	}

}
