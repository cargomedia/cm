<?php

class CM_Usertext_Cli extends CM_Cli_Runnable_Abstract {

	public function emojiUpdate() {

		$smileys = array();

		foreach (CM_Bootloader::getInstance()->getNamespaces() as $namespace) {
			$smileyPath = CM_Util::getNamespacePath($namespace) . 'layout/default/img/smiley/';

			if ($handle = opendir($smileyPath)) {
				while (false !== ($entry = readdir($handle))) {
					if (strpos($entry, '.') > 1) {
						$name = explode('.', $entry);
						$smileys[$name[0]] = array('name' => $name[0], 'extension' => $name[1]);
					}
				}
				closedir($handle);
			}
		}

		$insertSmileys = array();
		$counter = 0;
		foreach ($smileys as $smiley) {
			$counter++;
			$insertSmileys[] = "(':" . $smiley['name'] . ":',  '" . $smiley['name'] . "." . $smiley['extension'] . "')";

		}

		$sql = "INSERT INTO " . TBL_CM_SMILEY . " (`code` ,`file`) VALUES " . implode(',', $insertSmileys) . ";";
		CM_Mysql::exec($sql);

		$this->_getOutput()->writeln('Created emoji Table');
		$this->_getOutput()->writeln('Insert ' . $counter . ' smileys');
	}

	public static function getPackageName() {
		return 'usertext';
	}

}
