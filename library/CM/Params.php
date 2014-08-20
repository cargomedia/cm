<?php

class CM_Params extends CM_Class_Abstract {

    /**  @var array */
    private $_paramsOriginal = array();

    /**  @var array */
    private $_params = array();

    /** @var bool */
    private $_decode;

    /**
     * @param array|null $params
     * @param bool|null  $decode Defaults to true
     */
    public function __construct(array $params = null, $decode = null) {
        if (null === $decode) {
            $decode = true;
        }
        $this->_decode = (bool) $decode;
        $this->_paramsOriginal = (array) $params;
        $this->_params = (array) $params;
        if ($this->_decode) {
            $this->_params = array();
        }
    }

    /**
     * @param string     $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get($key, $default = null) {
        return $this->_get($key, $default);
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value) {
        if ($this->_decode) {
            $value = self::decode($value);
        }
        $this->_params[$key] = $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key) {
        return (array_key_exists($key, $this->_params) && null !== $this->_params[$key])
            || (array_key_exists($key, $this->_paramsOriginal) && null !== $this->_paramsOriginal[$key]);
    }

    /**
     * @return array
     */
    public function getAll() {
        if ($this->_decode) {
            foreach (array_diff_key($this->_paramsOriginal, $this->_params) as $key => $value) {
                $this->_params[$key] = self::decode($value);
            }
        }
        return $this->_params;
    }

    /**
     * @return array
     */
    public function getAllOriginal() {
        return $this->_paramsOriginal;
    }

    /**
     * @param string      $key
     * @param string|null $default
     * @return float
     */
    public function getFloat($key, $default = null) {
        $param = $this->_get($key, $default);
        return $this->_getFloat($param);
    }

    /**
     * @param string      $key
     * @param string|null $default
     * @return string
     */
    public function getString($key, $default = null) {
        $param = $this->_get($key, $default);
        return $this->_getString($param);
    }

    /**
     * @param string        $key
     * @param string[]|null $default
     * @return string[]
     */
    public function getStringArray($key, array $default = null) {
        return array_map(array($this, '_getString'), $this->getArray($key, $default));
    }

    /**
     * @param string      $key
     * @param string|null $default
     * @return int
     */
    public function getInt($key, $default = null) {
        $param = $this->_get($key, $default);
        return $this->_getInt($param);
    }

    /**
     * @param string     $key
     * @param int[]|null $default
     * @return int[]
     */
    public function getIntArray($key, array $default = null) {
        return array_map(array($this, '_getInt'), $this->getArray($key, $default));
    }

    /**
     * @param string $key
     * @param array  $default
     * @return array
     * @throws CM_Exception_InvalidParam
     */
    public function getArray($key, array $default = null) {
        $param = $this->_get($key, $default);
        if (!is_array($param)) {
            throw new CM_Exception_InvalidParam('Not an Array');
        }
        return (array) $param;
    }

    /**
     * @param string  $key
     * @param boolean $default
     * @return boolean
     * @throws CM_Exception_InvalidParam
     */
    public function getBoolean($key, $default = null) {
        $param = $this->_get($key, $default);
        if (1 === $param || '1' === $param || 'true' === $param) {
            $param = true;
        }
        if (0 === $param || '0' === $param || 'false' === $param) {
            $param = false;
        }
        if (!is_bool($param)) {
            throw new CM_Exception_InvalidParam('Not a boolean');
        }
        return (boolean) $param;
    }

    /**
     * @param string $key
     * @throws CM_Exception_InvalidParam
     * @return DateTime
     */
    public function getDateTime($key) {
        $value = $this->get($key);
        if (is_array($value) && isset($value['date']) && isset($value['timezone_type']) && isset($value['timezone'])) {
            $date = (string) $value['date'];
            $timezone = (string) $value['timezone'];
            $timezoneType = (int) $value['timezone_type'];
            switch ($timezoneType) {
                case 1:
                    $datetime = new DateTime($date . ' ' . $timezone);
                    break;
                case 2:
                    $datetime = new DateTime($date . ' ' . $timezone);
                    break;
                case 3:
                    $datetime = new DateTime($date, new DateTimeZone($timezone));
                    break;
                default:
                    throw new CM_Exception_InvalidParam('Invalid timezone type `' . $timezoneType . '`');
            }
            return $datetime;
        }
        return $this->getObject($key, 'DateTime');
    }

    /**
     * @param string   $key
     * @param int|null $default
     * @return int
     */
    public function getPage($key = 'page', $default = null) {
        if (null === $default) {
            $default = 1;
        }
        $page = $this->getInt($key, $default);
        $page = min(1000, $page);
        $page = max(1, $page);
        return $page;
    }

    /**
     * @param string       $key
     * @param string       $className
     * @param mixed|null   $default
     * @param Closure|null $getter
     * @throws CM_Exception_InvalidParam
     * @return object
     */
    public function getObject($key, $className, $default = null, Closure $getter = null) {
        if (!$getter) {
            $getter = function ($className, $param) {
                return new $className($param);
            };
        }
        $param = $this->_get($key, $default);
        if (!($param instanceof $className)) {
            if (is_object($param)) {
                throw new CM_Exception_InvalidParam(get_class($param) . ' is not a ' . $className);
            }
            return $getter($className, $param);
        }
        return $param;
    }

    /**
     * @param string $key
     * @return CM_Model_Entity_Abstract
     * @throws CM_Exception_Invalid
     */
    public function getEntity($key) {
        $param = $this->_get($key);
        if (!$param instanceof CM_Model_Entity_Abstract) {
            throw new CM_Exception_Invalid('Not a CM_Model_Entity_Abstract');
        }
        return $param;
    }

    /**
     * @param string $key
     * @return CM_Model_Abstract
     * @throws CM_Exception_Invalid
     */
    public function getModel($key) {
        $param = $this->_get($key);
        if (!($param instanceof CM_Model_Abstract)) {
            throw new CM_Exception_Invalid('Not a CM_Model_Abstract');
        }
        return $param;
    }

    /**
     * @param string $key
     * @return CM_Paging_Abstract
     * @throws CM_Exception_Invalid
     */
    public function getPaging($key) {
        $param = $this->_get($key);
        if (!$param instanceof CM_Paging_Abstract) {
            throw new CM_Exception_Invalid('Not a CM_Paging_Abstract');
        }
        return $param;
    }

    /**
     * @param string             $key
     * @param CM_Model_User|null $default
     * @throws CM_Exception_InvalidParam
     * @return CM_Model_User
     */
    public function getUser($key, CM_Model_User $default = null) {
        $param = $this->_get($key, $default);
        if (ctype_digit($param) || is_int($param)) {
            return CM_Model_User::factory($param);
        }
        if (!($param instanceof CM_Model_User)) {
            throw new CM_Exception_InvalidParam('Not a CM_Model_User');
        }
        return $param;
    }

    /**
     * @param string         $key
     * @param CM_Params|null $default
     * @return CM_Params
     * @throws CM_Exception_Invalid
     */
    public function getParams($key, CM_Params $default = null) {
        $param = $this->getObject($key, 'CM_Params', $default, function ($className, $param) {
            if (is_string($param)) {
                $json = (string) $param;
                try {
                    $array = CM_Params::decode($json, true);
                } catch (CM_Exception_Invalid $e) {
                    throw new CM_Exception_InvalidParam('Cannot decode input: ' . $e->getMessage());
                }
            } elseif (is_array($param)) {
                $array = $param;
            } else {
                throw new CM_Exception_InvalidParam('Unexpected input of type `' . gettype($param) . '` to create CM_Params');
            }
            return CM_Params::factory($array);
        });
        if (!($param instanceof CM_Params)) {
            throw new CM_Exception_Invalid('Not a CM_Params');
        }
        return $param;
    }

    /**
     * @param string $key
     * @return CM_Model_Location
     */
    public function getLocation($key) {
        return $this->getObject($key, 'CM_Model_Location');
    }

    /**
     * @param string $key
     * @return CM_Model_ActionLimit_Abstract
     */
    public function getActionLimit($key) {
        return $this->getObject($key, 'CM_Model_ActionLimit_Abstract');
    }

    /**
     * @param string                        $key
     * @param CM_Model_Language|string|null $default
     * @return CM_Model_Language
     */
    public function getLanguage($key, $default = null) {
        return $this->getObject($key, 'CM_Model_Language', $default);
    }

    /**
     * @param string                    $key
     * @param CM_Site_Abstract|int|null $default
     * @throws CM_Exception_InvalidParam
     * @return CM_Site_Abstract
     */
    public function getSite($key, $default = null) {
        $param = $this->_get($key, $default);
        if (ctype_digit($param) || is_int($param)) {
            return CM_Site_Abstract::factory($param);
        }
        if (!($param instanceof CM_Site_Abstract)) {
            throw new CM_Exception_InvalidParam('Not a CM_Site_Abstract');
        }
        return $param;
    }

    /**
     * @param string $key
     * @return CM_Model_Stream_Publish
     */
    public function getStreamPublish($key) {
        return $this->getObject($key, 'CM_Model_Stream_Publish');
    }

    /**
     * @param string $key
     * @return CM_Model_Stream_Subscribe
     */
    public function getStreamSubscribe($key) {
        return $this->getObject($key, 'CM_Model_Stream_Subscribe');
    }

    /**
     * @param string $key
     * @return CM_Model_StreamChannel_Video
     */
    public function getStreamChannelVideo($key) {
        return $this->getObject($key, 'CM_Model_StreamChannel_Video');
    }

    /**
     * @param string $key
     * @return CM_File
     */
    public function getFile($key) {
        return $this->getObject($key, 'CM_File');
    }

    /**
     * @param string $key
     * @return CM_Geo_Point
     */
    public function getGeoPoint($key) {
        return $this->getObject($key, 'CM_Geo_Point');
    }

    /**
     * @return mixed
     */
    public function shift() {
        return array_shift($this->_params);
    }

    /**
     * @param string $key
     */
    public function remove($key) {
        unset($this->_params[$key]);
    }

    /**
     * @param string $key
     * @param mixed  $default
     * @throws CM_Exception_InvalidParam
     * @return mixed
     */
    protected function _get($key, $default = null) {
        if (!$this->has($key) && $default === null) {
            throw new CM_Exception_InvalidParam("Param `$key` not set");
        }
        if (!$this->has($key) && $default !== null) {
            return $default;
        }
        if (!array_key_exists($key, $this->_params)) {
            $this->_params[$key] = self::decode($this->_paramsOriginal[$key]);
        }
        return $this->_params[$key];
    }

    /**
     * @param mixed $param
     * @return float
     * @throws CM_Exception_InvalidParam
     */
    private function _getFloat($param) {
        if (is_float($param) || is_int($param)) {
            return (float) $param;
        }
        if (is_string($param)) {
            if (preg_match('/^-?(?:\\d++\\.?+\\d*+|\\.\\d++)$/', $param)) {
                return (float) $param;
            }
        }
        throw new CM_Exception_InvalidParam('Not a float');
    }

    /**
     * @param mixed $param
     * @return string
     * @throws CM_Exception_InvalidParam
     */
    private function _getString($param) {
        if (!is_string($param)) {
            throw new CM_Exception_InvalidParam('Not a String');
        }
        return (string) $param;
    }

    /**
     * @param mixed $param
     * @return int
     * @throws CM_Exception_InvalidParam
     */
    private function _getInt($param) {
        if (!ctype_digit($param) && !is_int($param)) {
            throw new CM_Exception_InvalidParam('Not an Integer');
        }
        return (int) $param;
    }

    /**
     * @param mixed        $value
     * @param boolean|null $json
     * @throws CM_Exception_Invalid
     * @return string
     */
    public static function encode($value, $json = null) {
        if (is_array($value)) {
            $value = array_map('self::encode', $value);
        }
        if ($value instanceof CM_ArrayConvertible) {
            $array = $value->toArray();
            $array = array_map('self::encode', $array);
            $value = array_merge($array, array('_class' => get_class($value)));
        }
        if ($json) {
            if (is_object($value)) {
                $value = 'null';
            } else {
                $value = self::jsonEncode($value);
            }
        }
        return $value;
    }

    /**
     * @param string       $value
     * @param boolean|null $json
     * @throws CM_Exception_Invalid
     * @return mixed|false
     */
    public static function decode($value, $json = null) {
        if ($json) {
            $value = self::jsonDecode($value);
        }
        if (is_array($value) && isset($value['_class'])) {
            // CM_ArrayConvertible
            $className = (string) $value['_class'];
            unset($value['_class']);
            $value = call_user_func(array($className, 'fromArray'), $value);
            if (!$value) {
                return false;
            }
        }
        if (is_array($value)) {
            $value = array_map('self::decode', $value);
        }
        return $value;
    }

    /**
     * @param mixed     $value
     * @param bool|null $prettyPrint
     * @throws CM_Exception_Invalid
     * @return string
     */
    public static function jsonEncode($value, $prettyPrint = null) {
        $options = 0;
        if ($prettyPrint) {
            $options = $options | JSON_PRETTY_PRINT;
        }
        $value = json_encode($value, $options);
        if (json_last_error() > 0) {
            throw new CM_Exception_Invalid('Cannot json_encode value `' . CM_Util::var_line($value) . '`.');
        }
        return $value;
    }

    /**
     * @param string $value
     * @return mixed
     * @throws CM_Exception_Invalid
     */
    public static function jsonDecode($value) {
        $valueString = (string) $value;
        $value = json_decode($valueString, true);
        if (json_last_error() > 0) {
            throw new CM_Exception_Invalid('Cannot json_decode value `' . $valueString . '`.');
        }
        return $value;
    }

    /**
     * @param array|null $params
     * @param bool|null  $decode
     * @return static
     */
    public static function factory(array $params = null, $decode = null) {
        $params = (array) $params;
        $className = self::_getClassName();
        return new $className($params, $decode);
    }
}
