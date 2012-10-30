<?php

class CM_Params extends CM_Class_Abstract {

	/**  @var array */
	private $_paramsOriginal = array();

	/**  @var array */
	private $_params = array();

	/** @var bool */
	private $_decode;

	/**
	 * @param array $params OPTIONAL
	 * @param bool  $decode OPTIONAL
	 */
	public function __construct(array $params = array(), $decode = true) {
		$this->_decode = (bool) $decode;
		$this->_paramsOriginal = $params;
		$this->_params = $params;
		if ($this->_decode) {
			foreach ($this->_params as $key => &$param) {
				$param = self::decode($param);
			}
		}
	}

	/**
	 * @param string $key
	 * @param mixed  $default
	 * @return mixed
	 */
	protected function _get($key, $default = null) {
		if (!$this->has($key) && $default === null) {
			throw new CM_Exception_InvalidParam("Param `$key` not set");
		}
		if (!$this->has($key) && $default !== null) {
			return $default;
		}
		return $this->_params[$key];
	}

	/**
	 * @param            $key
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
	 * Whether a param is set and not NULL
	 * @param string $key
	 * @return bool
	 */
	public function has($key) {
		return (array_key_exists($key, $this->_params) && null !== $this->_params[$key]);
	}

	/**
	 * @return array
	 */
	public function getAll() {
		return $this->_params;
	}

	/**
	 * @return array
	 */
	public function getAllOriginal() {
		return $this->_paramsOriginal;
	}

	private function _getFloat($param) {
		if (is_float($param)) {
			return $param;
		}
		if (!preg_match('/^[\d]*?(\.[\d]*)?$/', $param)) {
			throw new CM_Exception_InvalidParam('Not a float');
		}
		return (float) $param;
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

	private function _getString($param) {
		if (!is_string($param)) {
			throw new CM_Exception_InvalidParam('Not a String');
		}
		return (string) $param;
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

	private function _getInt($param) {
		if (!ctype_digit($param) && !is_int($param)) {
			throw new CM_Exception_InvalidParam('Not an Integer');
		}
		return (int) $param;
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
	 * @param string $key
	 * @param int[]  $default OPTIONAL
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
	 * @param int    $default
	 * @return int
	 */
	public function getPage($key = 'page', $default = 1) {
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
	protected function _getObject($key, $className, $default = null, Closure $getter = null) {
		if (!$getter) {
			$getter = function($className, $param) {
				return new $className($param);
			};
		}
		$param = $this->_get($key, $default);
		if (ctype_digit($param) || is_int($param)) {
			return $getter($className, $param);
		}
		if (!($param instanceof $className)) {
			throw new CM_Exception_InvalidParam('Not a ' . $className);
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
	 * @param string                   $key
	 * @param CM_Model_User|null       $default
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
	 * @param string $key
	 * @return CM_Model_Location
	 * @throws CM_Exception_InvalidParam
	 */
	public function getLocation($key) {
		return $this->_getObject($key, 'CM_Model_Location');
	}

	/**
	 * @param CM_Model_Language|string      $key
	 * @param CM_Model_Language|string|null $default
	 * @return CM_Model_Language
	 */
	public function getLanguage($key, $default = null) {
		return $this->_getObject($key, 'CM_Model_Language', $default);
	}

	/**
	 * @param CM_Site_Abstract|int      $key
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
	 * @throws CM_Exception_InvalidParam
	 */
	public function getStreamPublish($key) {
		return $this->_getObject($key, 'CM_Model_Stream_Publish');
	}

	/**
	 * @param string $key
	 * @return CM_Model_Stream_Subscribe
	 * @throws CM_Exception_InvalidParam
	 */
	public function getStreamSubscribe($key) {
		return $this->_getObject($key, 'CM_Model_Stream_Subscribe');
	}

	/**
	 * @param string $key
	 * @return CM_Model_StreamChannel_Video
	 * @throws CM_Exception_InvalidParam
	 */
	public function getStreamChannelVideo($key) {
		return $this->_getObject($key, 'CM_Model_StreamChannel_Video');
	}

	/**
	 * @param mixed   $value
	 * @param boolean $json OPTIONAL
	 * @return string
	 */
	public static function encode($value, $json = false) {
		if (is_array($value)) {
			$value = array_map('self::encode', $value);
		}
		if ($value instanceof CM_ArrayConvertible) {
			$array = $value->toArray();
			$array = array_map('self::encode', $array);
			$value = array_merge($array, array('_class' => get_class($value)));
		}
		if ($json) {
			$value = json_encode($value);
			if (json_last_error() > 0) {
				throw new CM_Exception_Invalid('Cannot json_encode value `' . CM_Util::var_line($value) . '`.');
			}
		}
		return $value;
	}

	/**
	 * @param string  $value
	 * @param boolean $json OPTIONAL
	 * @return mixed|false
	 */
	public static function decode($value, $json = false) {
		if ($json) {
			$value = json_decode($value, true);
			if (json_last_error() > 0) {
				throw new CM_Exception_Invalid('Cannot json_decode value `' . CM_Util::var_line($value) . '`.');
			}
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
	 * @param array $params
	 * @param bool  $decode OPTIONAL
	 * @return CM_Params
	 */
	public static function factory(array $params = array(), $decode = true) {
		$className = self::_getClassName();
		return new $className($params, $decode);
	}

}
