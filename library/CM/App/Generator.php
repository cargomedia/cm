<?php

class CM_App_Generator {

	/**
	 * @param string        $className
	 * @param string|null   $parentClass
	 * @return CM_File_Php
	 */
	public function createClassFilePhp($className, $parentClass = null) {
		$parts = explode('_', $className);
		$namespace = array_shift($parts);
		$type = array_shift($parts);
		if (!$parentClass) {
			$parentClass = $this->_getParentClass($namespace, $type);
		}
		$file = CM_File_Php::createLibraryClass($className, $parentClass);
		$reflectionClass = new ReflectionClass($parentClass);
		foreach ($reflectionClass->getMethods(ReflectionMethod::IS_ABSTRACT) as $method) {
			$file->setMethodFrom($parentClass, $method->getName());
		}
		return $file;
	}

	/**
	 * @param string $className
	 * @return CM_File_Javascript
	 */
	public function createClassFileJavascript($className) {
		$file = CM_File_Javascript::createViewClass($className);
		return $file;
	}

	/**
	 * @param string $className
	 * @return CM_File
	 */
	public function createViewTemplate($className) {
		$parts = explode('_', $className);
		$namespace = array_shift($parts);
		$viewType = array_shift($parts);
		$pathRelative = implode('_', $parts);
		$layoutPath = CM_Util::getNamespacePath($namespace) . 'layout/default/' . $viewType . '/' . $pathRelative . '/';
		CM_Util::mkDir($layoutPath);
		return CM_File::create($layoutPath . 'default.tpl');
	}

	/**
	 * @param string $className
	 * @return CM_File
	 */
	public function createViewStylesheets($className) {
		$parts = explode('_', $className);
		$namespace = array_shift($parts);
		$viewType = array_shift($parts);
		$pathRelative = implode('_', $parts);
		$layoutPath = CM_Util::getNamespacePath($namespace) . 'layout/default/' . $viewType . '/' . $pathRelative . '/';
		return CM_File::create($layoutPath . 'default.less');
	}

	/**
	 * @param string $bootloaderClass
	 * @return CM_File_Php
	 */
	public function createHttpEntryPoint($bootloaderClass) {
		$body = <<< 'EOD'
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
$bootloader = new ${BOOTLOADER_CLASS}(dirname(__DIR__) . '/', ${DIR_LIBRARY});
$bootloader->load(array('autoloader', 'constants', 'exceptionHandler', 'errorHandler', 'defaults'));

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$headers = apache_request_headers();
$body = file_get_contents('php://input');

$request = CM_Request_Abstract::factory($method, $uri, $headers, $body);
$response = CM_Response_Abstract::factory($request);

$response->process();
$response->sendHeaders();
echo $response->getContent();
exit;

EOD;
		$body = str_replace('${BOOTLOADER_CLASS}', $bootloaderClass, $body);
		$body = str_replace('${DIR_LIBRARY}', var_export(DIR_LIBRARY, true), $body);
		CM_Util::mkDir(DIR_ROOT . 'public/');
		return CM_File_Php::create(DIR_ROOT . 'public/index.php', $body);
	}

	/**
	 * @param string $bootloaderClass
	 * @return CM_File_Php
	 */
	public function createScriptEntryPoint($bootloaderClass) {
		$body = <<< 'EOD'
#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
$bootloader = new ${BOOTLOADER_CLASS}(dirname(__DIR__) . '/', ${DIR_LIBRARY});
$bootloader->setEnvironment('cli');
$bootloader->load(array('autoloader', 'constants', 'exceptionHandler', 'errorHandler', 'defaults'));

$manager = new CM_Cli_CommandManager();
$returnCode = $manager->run(new CM_Cli_Arguments($argv));
exit($returnCode);

EOD;
		$body = str_replace('${BOOTLOADER_CLASS}', $bootloaderClass, $body);
		$body = str_replace('${DIR_LIBRARY}', var_export(DIR_LIBRARY, true), $body);
		CM_Util::mkDir(DIR_ROOT . 'scripts/');
		return CM_File_Php::create(DIR_ROOT . 'scripts/cm.php', $body);
	}

	/**
	 * @param string $viewNamespace
	 * @param string $type
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	private function _getParentClass($viewNamespace, $type) {
		$namespaces = array_reverse(CM_Bootloader::getInstance()->getNamespaces());
		$position = array_search($viewNamespace, $namespaces);
		if (false === $position) {
			throw new CM_Exception_Invalid('Namespace `' . $viewNamespace . '` not found within `' . implode(', ', $namespaces) . '` namespaces.');
		}
		$namespaces = array_splice($namespaces, $position);
		foreach ($namespaces as $namespace) {
			$className = $namespace . '_' . $type . '_Abstract';
			if (class_exists($className)) {
				return $className;
			}
		}
		throw new CM_Exception_Invalid('No abstract class found for `' . $type . '` type within `' . implode(', ', $namespaces) . '` namespaces.');
	}
}
