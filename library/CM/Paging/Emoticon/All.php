<?php

class CM_Paging_Emoticon_All extends CM_Paging_Emoticon_Abstract {

    public function __construct() {
        $emoticonList = $this->getEmoticonList();
        $source = new CM_PagingSource_Array($emoticonList);
        $source->enableCacheLocal();
        parent::__construct($source);
        $this->_checkEmoticonValidity();
    }

    public function getEmoticonList() {
        $emoticonList = array();

        /** @var CM_File[] $configFiles */
        $configFiles = [];
        $modules = CM_Bootloader::getInstance()->getModules();
        sort($modules);
        $id = 0;
        foreach ($modules as $namespace) {
            $emoticonPath = CM_Util::getModulePath($namespace) . 'layout/default/resource/img/emoticon/';
            $paths = glob($emoticonPath . '*');
            foreach ($paths as $path) {
                $file = new CM_File($path);
                $name = strtolower($file->getFileNameWithoutExtension());
                if ('json' == $file->getExtension()) {
                    $configFiles[$name] = $file;
                } else {
                    $emoticonList[$name] = array('id'             => $id++, 'code' => ':' . $name . ':', 'file' => $file->getFileName(),
                                                 'codeAdditional' => null);
                }
            }
        }

        foreach ($configFiles as $name => $file) {
            $config = CM_Params::jsonDecode($file->read());
            if (isset($emoticonList[$name])) {
                $emoticonList[$name]['codeAdditional'] = $config['codeAdditional'];
            } else {
                //$output->writeln('WARNING: No emoticon image is specified for:' . $name);
            }
        }
        return $emoticonList;
    }

    private function _checkEmoticonValidity() {
        $codes = array();
        foreach ($this as $emoticon) {
            if (false !== array_search('', $emoticon['codes'])) {
                //$output->writeln('WARNING: Empty emoticon with ID `' . $emoticon['id'] . '`.');
                return;
            }
            $codes = array_merge($codes, $emoticon['codes']);
        }
        for ($i = 0; $i < count($codes); $i++) {
            for ($j = $i + 1; $j < count($codes); $j++) {
                if (false !== strpos($codes[$i], $codes[$j]) || false !== strpos($codes[$j], $codes[$i])) {
                    //$output->writeln('WARNING: Emoticon intersection: `' . $codes[$i] . '` <-> `' . $codes[$j] . '`.');
                }
            }
        }
    }
}
