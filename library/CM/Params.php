<?php

class CM_Params extends CM_Class_Abstract implements CM_Debug_DebugInfoInterface {

    /** @var array */
    private $_params;

    /**
     * @param array|null   $params
     * @param boolean|null $encoded
     * @throws CM_Exception_Invalid
     */
    public function __construct(array $params = null, $encoded = null) {
        $params = $params ?: array();
        if (null === $encoded) {
            if (!empty($params)) {
                throw new CM_Exception_Invalid('Params must be declared encoded or decoded');
            }
        }
        $encoded = (boolean) $encoded;
        $this->_params = \Functional\map($params, function ($value) use ($encoded) {
            if ($encoded) {
                return ['encoded' => $value, 'decoded' => null];
            } else {
                return ['encoded' => null, 'decoded' => $value];
            }
        });
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
        $this->_params[$key] = ['decoded' => $value, 'encoded' => null];
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key) {
        return array_key_exists($key, $this->_params)
        && (null !== $this->_params[$key]['decoded'] || null !== $this->_params[$key]['encoded']);
    }

    /**
     * @param CM_Params $params
     * @return CM_Params
     */
    public function merge(CM_Params $params) {
        $new = static::factory($this->getParamsDecoded(), false);
        foreach ($params->getParamsDecoded() as $key => $value) {
            $new->set($key, $value);
        }
        return $new;
    }

    /**
     * @return array
     */
    public function getParamsDecoded() {
        $result = array();
        foreach ($this->_params as $key => &$param) {
            if (null === $param['decoded']) {
                $param['decoded'] = static::decode($param['encoded']);
            }
            $result[$key] = $param['decoded'];
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getParamsEncoded() {
        $result = array();
        foreach ($this->_params as $key => &$param) {
            if (null === $param['encoded']) {
                $param['encoded'] = static::encode($param['decoded']);
            }
            $result[$key] = $param['encoded'];
        }
        return $result;
    }

    /**
     * @return string[]
     */
    public function getParamNames() {
        return array_keys($this->_params);
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
                    throw new CM_Exception_InvalidParam('Invalid timezone type', null, ['timezoneType' => $timezoneType]);
            }
            return $datetime;
        }
        return $this->getObject($key, 'DateTime');
    }

    /**
     * @param string $key
     * @return DateInterval
     */
    public function getDateInterval($key) {
        return $this->getObject($key, 'DateInterval');
    }

    /**
     * @param string $key
     * @return DateTimeZone
     */
    public function getDateTimeZone($key) {
        return $this->getObject($key, 'DateTimeZone');
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
                $arguments = (array) $param;
                $reflectionClass = new ReflectionClass($className);
                $constructor = $reflectionClass->getConstructor();

                if ($constructor->getNumberOfRequiredParameters() > 1) {
                    $namedArgs = new CM_Util_NamedArgs();
                    try {
                        $arguments = $namedArgs->matchNamedArgs($constructor, $arguments);
                    } catch (CM_Exception_Invalid $ex) {
                        throw new CM_Exception_InvalidParam('Not enough parameters', null, [
                            'parameters' => $param,
                            'className'  => $className,
                        ]);
                    }
                }

                return $reflectionClass->newInstanceArgs($arguments);
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
                    throw new CM_Exception_InvalidParam('Cannot decode input', null, ['message' => $e->getMessage()]);
                }
            } elseif (is_array($param)) {
                $array = $param;
            } else {
                throw new CM_Exception_InvalidParam('Unexpected type of arguments', null, ['type' => gettype($param)]);
            }
            return CM_Params::factory($array, false);
        });
        if (!($param instanceof CM_Params)) {
            throw new CM_Exception_Invalid('Not a CM_Params');
        }
        return $param;
    }

    /**
     * @param string $key
     * @return CM_Model_Location
     * @throws CM_Exception_InvalidParam
     */
    public function getLocation($key) {
        try {
            return $this->getObject($key, 'CM_Model_Location');
        } catch (CM_Location_InvalidLevelException $e) {
            throw new CM_Exception_InvalidParam($e->getMessage(), null, $e->getMetaInfo());
        }
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
     * @return CM_Model_StreamChannel_Abstract
     */
    public function getStreamChannel($key) {
        return $this->getObject($key, 'CM_Model_StreamChannel_Abstract');
    }

    /**
     * @param string $key
     * @return CM_Model_StreamChannel_Media
     */
    public function getStreamChannelMedia($key) {
        return $this->getObject($key, 'CM_Model_StreamChannel_Media');
    }

    /**
     * @param string $key
     * @return CM_Janus_StreamChannel
     */
    public function getStreamChannelJanus($key) {
        return $this->getObject($key, 'CM_Janus_StreamChannel');
    }

    /**
     * @param string $key
     * @return CM_StreamChannel_Definition
     */
    public function getStreamChannelDefinition($key) {
        return $this->getObject($key, 'CM_StreamChannel_Definition');
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
     * @param string $key
     * @return CM_Geometry_Vector2
     */
    public function getGeometryVector2($key) {
        return $this->getObject($key, 'CM_Geometry_Vector2');
    }

    /**
     * @param string $key
     * @return CM_Geometry_Vector3
     */
    public function getGeometryVector3($key) {
        return $this->getObject($key, 'CM_Geometry_Vector3');
    }

    /**
     * @param $key
     * @return CM_Mail_Message
     */
    public function getMailMessage($key) {
        return $this->getObject($key, 'CM_Mail_Message');
    }

    /**
     * @return mixed
     */
    public function shift() {
        $param = array_shift($this->_params);
        return null !== $param['decoded'] ? $param['decoded'] : self::decode($param['encoded']);
    }

    /**
     * @param string $key
     */
    public function remove($key) {
        unset($this->_params[$key]);
    }

    /**
     * @return string
     */
    public function getDebugInfo() {
        try {
            $variableInspector = new CM_Debug_VariableInspector();
            return $variableInspector->getDebugInfo($this->getParamsDecoded(), ['recursive' => true]);
        } catch (Exception $e) {
            return '[Cannot dump params: `' . $e->getMessage() . '`]';
        }
    }

    /**
     * @param string $key
     * @return CM_Session
     * @throws CM_Exception_InvalidParam
     */
    public function getSession($key) {
        $key = (string) $key;
        return $this->getObject($key, 'CM_Session', null, function ($className, $param) use ($key) {
            if (is_string($param)) {
                try {
                    return new CM_Session($param);
                } catch (CM_Exception_UnknownSessionId $e) {
                    throw new CM_Exception_InvalidParam('Session is not found', null, ['key' => $key]);
                }
            }
            throw new CM_Exception_InvalidParam('Invalid param type for session', null, ['key' => $key]);
        });
    }

    /**
     * @param string $key
     * @param mixed  $default
     * @throws CM_Exception_InvalidParam
     * @return mixed
     */
    protected function _get($key, $default = null) {
        if (!$this->has($key) && $default === null) {
            throw new CM_Exception_InvalidParam('Param not set', null, ['key' => $key]);
        }
        if (!$this->has($key) && $default !== null) {
            return $default;
        }
        $param = &$this->_params[$key];
        if (null === $param['decoded']) {
            $param['decoded'] = self::decode($param['encoded']);
        }
        return $param['decoded'];
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
     * @return array|string
     */
    public static function encode($value, $json = null) {
        if (is_array($value)) {
            $result = array_map('self::encode', $value);
        } elseif ($value instanceof CM_ArrayConvertible || $value instanceof JsonSerializable) {
            $result = ['_class' => get_class($value)];
            if ($value instanceof CM_ArrayConvertible) {
                $array = $value->toArray();
                $result = array_merge($result, self::encode($array));
            }
            if ($value instanceof JsonSerializable) {
                $array = $value->jsonSerialize();
                if (is_array($array)) {
                    $result = array_merge($result, self::encode($array));
                }
            }
        } else {
            $result = $value;
        }

        if ($json) {
            if (is_object($result)) {
                $result = 'null';
            } else {
                $result = CM_Util::jsonEncode($result);
            }
        }
        return $result;
    }

    /**
     * @param CM_ArrayConvertible $object
     * @return string JSON
     */
    public static function encodeObjectId(CM_ArrayConvertible $object) {
        $array = $object->toArray();
        $value = array_merge($array, array('_class' => get_class($object)));
        return CM_Util::jsonEncode($value);
    }

    /**
     * @param string       $value
     * @param boolean|null $json
     * @return mixed|false
     */
    public static function decode($value, $json = null) {
        if ($json) {
            $value = CM_Util::jsonDecode($value);
        }
        if (is_array($value) && isset($value['_class']) && is_subclass_of($value['_class'], 'CM_ArrayConvertible')) {
            $className = (string) $value['_class'];
            unset($value['_class']);
            if (!empty($value)) {
                $value = self::decode($value);
            }
            /** @var CM_ArrayConvertible $className */
            $value = $className::fromArray($value);
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
     * @deprecated use CM_Util::jsonEncode()
     */
    public static function jsonEncode($value, $prettyPrint = null) {
        return CM_Util::jsonEncode($value, $prettyPrint);
    }

    /**
     * @param string $value
     * @return mixed
     * @throws CM_Exception_Invalid
     * @deprecated use CM_Util::jsonDecode()
     */
    public static function jsonDecode($value) {
        return CM_Util::jsonDecode($value);
    }

    /**
     * @param array|null $params
     * @param bool|null  $encoded
     * @return static
     */
    public static function factory(array $params = null, $encoded = null) {
        $className = self::_getClassName();
        return new $className($params, $encoded);
    }
}
