<?php

class CM_App_Resource_Javascript_Library extends CM_App_Resource_Javascript_Abstract {

	/**
	 * @param CM_Site_Abstract $site
	 */
	public function __construct(CM_Site_Abstract $site) {
		$content = '';
		$pathsUnsorted = CM_Util::rglobLibraries('*.js', $site);
		foreach (CM_Util::getClasses($pathsUnsorted) as $path => $className) {
			$content .= new CM_File($path);
		}
		$internal = new CM_App_Resource_Javascript_Internal($site);
		$content .= $internal->get();
		$this->_content = $content;
	}
}
