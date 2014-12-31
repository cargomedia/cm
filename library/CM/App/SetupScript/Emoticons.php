<?php

class CM_App_SetupScript_Emoticons extends CM_Provision_Script_OptionBased implements CM_Provision_Script_UnloadableInterface {

    protected function _isLoaded() {
        return false;
    }

    public function unload(CM_OutputStream_Interface $output) {
        CM_Db_Db::exec('TRUNCATE TABLE `cm_emoticon`');
    }

    public function load(CM_OutputStream_Interface $output) {
        $this->emoticonRefresh($output);
        $this->checkEmoticonValidity($output);
        $this->_setLoaded(true);
    }

    /**
     * @param CM_OutputStream_Interface $output
     * @throws CM_Exception_Invalid
     */
    public function emoticonRefresh(CM_OutputStream_Interface $output) {
        $emoticonList = array();

        /** @var CM_File[] $configFiles */
        $configFiles = [];
        $modules = CM_Bootloader::getInstance()->getModules();
        sort($modules);
        foreach ($modules as $namespace) {
            $emoticonPath = CM_Util::getModulePath($namespace) . 'layout/default/resource/img/emoticon/';
            $paths = glob($emoticonPath . '*');
            foreach ($paths as $path) {
                $file = new CM_File($path);
                $name = strtolower($file->getFileNameWithoutExtension());
                if ('json' == $file->getExtension()) {
                    $configFiles[$name] = $file;
                } else {
                    $emoticonList[$name] = array('name' => $name, 'fileName' => $file->getFileName(), 'codeAdditional' => null);
                }
            }
        }

        foreach ($configFiles as $name => $file) {
            $config = CM_Params::jsonDecode($file->read());
            if (isset($emoticonList[$name])) {
                $emoticonList[$name]['codeAdditional'] = $config['codeAdditional'];
            } else {
                $output->writeln('WARNING: No emoticon image is specified for:' . $name);
            }
        }

        $insertList = array();
        foreach ($emoticonList as $emoticon) {
            $insertList[] = array(':' . $emoticon['name'] . ':', $emoticon['fileName'], $emoticon['codeAdditional']);
        }

        CM_Db_Db::insertIgnore('cm_emoticon', array('code', 'file', 'codeAdditional'), $insertList);
        $output->writeln('Updated ' . count($insertList) . ' emoticons.');
    }

    /**
     * @param CM_OutputStream_Interface $output
     */
    public function checkEmoticonValidity(CM_OutputStream_Interface $output) {
        $paging = new CM_Paging_Emoticon_All();
        $codes = array();
        foreach ($paging as $emoticon) {
            if (false !== array_search('', $emoticon['codes'])) {
                $output->writeln('WARNING: Empty emoticon with ID `' . $emoticon['id'] . '`.');
                return;
            }
            $codes = array_merge($codes, $emoticon['codes']);
        }
        for ($i = 0; $i < count($codes); $i++) {
            for ($j = $i + 1; $j < count($codes); $j++) {
                if (false !== strpos($codes[$i], $codes[$j]) || false !== strpos($codes[$j], $codes[$i])) {
                    $output->writeln('WARNING: Emoticon intersection: `' . $codes[$i] . '` <-> `' . $codes[$j] . '`.');
                }
            }
        }
    }
}
