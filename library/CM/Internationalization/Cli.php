<?php

class CM_Internationalization_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @param string    $abbreviation
     * @param string    $name
     * @param bool|null $enabled
     */
    public function createLanguage($abbreviation, $name, $enabled = null) {
        CM_Model_Language::create($name, $abbreviation, $enabled);
        $this->_getOutput()->writeln($name . ' (' . $abbreviation . ') language created');
    }

    public static function getPackageName() {
        return 'i18n';
    }
}
