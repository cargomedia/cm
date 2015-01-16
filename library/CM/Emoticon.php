<?php

class CM_Emoticon extends CM_Class_Abstract {

    /** @var string[] */
    private $_codes;

    /** @var string */
    private $_fileName;

    /** @var string */
    private $_name;

    /**
     * @param string     $name
     * @param array|null $data
     * @throws CM_Exception_Nonexistent
     */
    public function __construct($name, array $data = null) {
        $this->_name = (string) $name;
        $this->_load($data);
    }

    /**
     * @return string[]
     */
    public function getCodes() {
        return $this->_codes;
    }

    /**
     * @return string
     */
    public function getDefaultCode() {
        return $this->_codes[0];
    }

    /**
     * @return string
     */
    public function getFileName() {
        return $this->_fileName;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * @param array|null $data
     * @throws CM_Exception_Invalid
     */
    protected function _load(array $data = null) {
        if (null === $data) {
            $dataList = static::getEmoticonData();
            $name = $this->getName();
            if (empty($dataList[$name])) {
                throw new CM_Exception_Invalid('Nonexistent Emoticon', ['name' => $name]);
            }
            $data = $dataList[$name];
        }
        $this->_fileName = $data['fileName'];
        $this->_codes = $data['codes'];
    }

    /**
     * @return array[]
     */
    public static function getEmoticonData() {
        $cache = CM_Cache_Local::getInstance();
        $cacheKey = CM_CacheConst::Emoticons;
        if (false === $emoticonData = $cache->get($cacheKey)) {
            $emoticonData = static::_readEmoticonData();
            $cache->set($cacheKey, $emoticonData, 0);
        }
        return $emoticonData;
    }

    /**
     * @param string $code
     * @return CM_Emoticon|null
     */
    public static function findCode($code) {
        $emoticonData = static::getEmoticonData();
        $emoticon = \Functional\first($emoticonData, function ($emoticonData) use ($code) {
            return false !== array_search($code, $emoticonData['codes']);
        });
        if ($emoticon) {
            return new static($emoticon['name'], $emoticon);
        }
        return null;
    }

    /**
     * @param string $name
     * @return CM_Emoticon|null
     */
    public static function findName($name) {
        $emoticonData = static::getEmoticonData();
        if (array_key_exists($name, $emoticonData)) {
            return new static($name, $emoticonData[$name]);
        }
        return null;
    }

    /**
     * @throws CM_Exception
     */
    public static function validateData() {
        self::_readEmoticonData();
    }

    /**
     * @return array[]
     * @throws CM_Exception
     */
    private static function _readEmoticonData() {
        /** @var CM_File[] $configurationFiles */
        $configurationFiles = [];
        /** @var CM_File[] $imageFiles */
        $imageFiles = [];
        $bootloader = CM_Bootloader::getInstance();
        foreach ($bootloader->getModules() as $namespace) {
            $emoticonPath = CM_Util::getModulePath($namespace) . 'layout/default/resource/img/emoticon/';
            $emoticonDir = new CM_File($emoticonPath);
            foreach ($emoticonDir->listFiles(true) as $file) {
                $name = strtolower($file->getFileNameWithoutExtension());
                if ('json' === $file->getExtension()) {
                    $configurationFiles[$name] = $file;
                } else {
                    $imageFiles[$name] = $file;
                }
            }
        }
        $emoticonData = [];
        $codeList = [];
        foreach ($imageFiles as $name => $file) {
            $emoticonData[$name] = ['name' => $name, 'fileName' => $file->getFileName(), 'codes' => [":{$name}:"]];
            $codeList[":{$name}:"] = $name;
        }
        foreach ($configurationFiles as $name => $file) {
            $additionalCodes = CM_Params::jsonDecode($file->read())['additionalCodes'];
            foreach ($additionalCodes as $code) {
                if (!array_key_exists($code, $codeList)) {
                    $codeList[$code] = $name;
                    $emoticonData[$name]['codes'][] = $code;
                } else {
                    throw new CM_Exception("Emoticon codes overlap",
                        [
                            'overlapping emoticons' => [$name, $codeList[$code]],
                            'code'                  => $code
                        ]);
                }
            }
        }
        return $emoticonData;
    }
}
