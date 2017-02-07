<?php

class CM_StreamChannel_Definition implements CM_ArrayConvertible, JsonSerializable {

    /** @var string */
    private $_key;

    /** @var int */
    private $_type;

    /** @var int */
    private $_adapterType;

    /**
     * @param string   $key
     * @param int      $type
     * @param int|null $adapterType
     */
    public function __construct($key, $type, $adapterType = null) {
        $type = (int) $type;
        $key = (string) $key;
        if (null !== $adapterType) {
            $adapterType = (int) $adapterType;
        }
        $this->_type = $type;
        $this->_key = $key;
        $this->_adapterType = $adapterType;
    }

    /**
     * @return string
     */
    public function getKey() {
        return $this->_key;
    }

    /**
     * @return int
     */
    public function getType() {
        return $this->_type;
    }

    /**
     * @return int|null
     */
    public function getAdapterType() {
        return $this->_adapterType;
    }

    /**
     * @return CM_Model_StreamChannel_Abstract|null
     */
    public function findStreamChannel() {
        return CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($this->getKey(), $this->getAdapterType());
    }

    /**
     * @return boolean
     */
    public function exists() {
        return null !== self::findStreamChannel();
    }

    /**
     * @return CM_Model_StreamChannel_Abstract
     * @throws CM_Exception
     */
    public function getStreamChannel() {
        $channel = self::findStreamChannel();
        if (null === $channel) {
            throw new CM_Exception('StreamChannel doesn\'t exist', null, ['key' => $this->getKey()]);
        }
        return $channel;
    }

    public function jsonSerialize() {
        return [
            'key'         => $this->getKey(),
            'type'        => $this->getType(),
            'adapterType' => $this->getAdapterType(),
        ];
    }

    public function toArray() {
        return $this->jsonSerialize();
    }

    public static function fromArray(array $array) {
        return new self($array['key'], $array['type'], $array['adapterType']);
    }
}
