<?php

class CM_App_Cli extends CM_Cli_Runnable_Abstract {

	public function setup() {
		CM_App::getInstance()->setupFilesystem();
	}

	public function fillCache() {
		/** @var CM_Asset_Javascript_Abstract[] $resources */
		$resources = array();
		$siteClassNames = CM_Site_Abstract::getClassChildren();
		foreach ($siteClassNames as $siteClassName) {
			/** @var CM_Site_Abstract $site */
			$site = new $siteClassName();
			$resources[] = new CM_Asset_Javascript_Internal($site);
			$resources[] = new CM_Asset_Javascript_Library($site);
			$resources[] = new CM_Asset_Javascript_VendorAfterBody($site);
			$resources[] = new CM_Asset_Javascript_VendorBeforeBody($site);
		}
		foreach (new CM_Paging_Language_All() as $language) {
			$resources[] = new CM_Asset_Javascript_Translations($language);
		}
		foreach ($resources as $resource) {
			$resource->get(true);
		}
		$this->_getOutput()->writeln('Cached ' . count($resources) . ' resources.');
	}

	public function generateConfig() {
		// Create class types and action verbs config PHP
		$fileHeader = '<?php' . PHP_EOL;
		$fileHeader .= '// This is autogenerated action verbs config file. You should not adjust changes manually.' . PHP_EOL;
		$fileHeader .= '// You should adjust TYPE constants and regenerate file using `config generate` command' . PHP_EOL;
		$path = DIR_ROOT . 'resources/config/internal.php';
		$classTypesConfig = CM_App::getInstance()->generateConfigClassTypes();
		$actionVerbsConfig = CM_App::getInstance()->generateConfigActionVerbs();
		CM_File::create($path, $fileHeader . $classTypesConfig . PHP_EOL . PHP_EOL . $actionVerbsConfig . PHP_EOL);
		$this->_getOutput()->writeln('Created `' . $path . '`');

		// Create model class types and action verbs config JS
		$path = DIR_ROOT . 'resources/config/js/internal.js';
		$modelTypesConfig = 'cm.model.types = ' . CM_Params::encode(CM_App::getInstance()->getClassTypes('CM_Model_Abstract'), true) . ';';
		$actionVerbs = array();
		foreach (CM_App::getInstance()->getActionVerbs() as $verb) {
			$actionVerbs[$verb['name']] = $verb['value'];
		}
		$actionVerbsConfig = 'cm.action.verbs = ' . CM_Params::encode($actionVerbs, true) . ';';
		CM_File::create($path, $modelTypesConfig . PHP_EOL . $actionVerbsConfig . PHP_EOL);
		$this->_getOutput()->writeln('Created `' . $path . '`');
	}

	public static function getPackageName() {
		return 'app';
	}
}
