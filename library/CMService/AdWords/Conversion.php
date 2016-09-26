<?php

class CMService_AdWords_Conversion {

    /** @var int|null */
    protected $_id;

    /** @var string|null */
    protected $_color, $_conversionCurrency, $_format, $_label, $_language;

    /** @var float|null */
    protected $_conversionValue;

    /** @var array|null */
    protected $_customParameterList;

    /** @var bool|null */
    protected $_remarketingOnly;

    /**
     * @return string|null
     */
    public function getColor() {
        return $this->_color;
    }

    /**
     * @param string|null $color
     * @return $this
     */
    public function setColor($color) {
        if (null !== $color) {
            $color = (string) $color;
        }
        $this->_color = $color;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getConversionCurrency() {
        return $this->_conversionCurrency;
    }

    /**
     * @param string|null $conversionCurrency
     * @return $this
     */
    public function setConversionCurrency($conversionCurrency) {
        if (null !== $conversionCurrency) {
            $conversionCurrency = (string) $conversionCurrency;
        }
        $this->_conversionCurrency = $conversionCurrency;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getConversionValue() {
        return $this->_conversionValue;
    }

    /**
     * @param float|null $conversionValue
     * @return $this
     */
    public function setConversionValue($conversionValue) {
        if (null !== $conversionValue) {
            $conversionValue = (float) $conversionValue;
        }
        $this->_conversionValue = $conversionValue;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getCustomParameter($name) {
        $name = (string) $name;
        if (!isset($this->_customParameterList[$name])) {
            return null;
        }
        return $this->_customParameterList[$name];
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return $this
     */
    public function setCustomParameter($name, $value) {
        $name = (string) $name;
        $customParameterList = (array) $this->getCustomParameterList();
        if (null === $value) {
            unset($customParameterList[$name]);
        } else {
            $customParameterList[$name] = $value;
        }
        return $this->setCustomParameterList($customParameterList);
    }

    /**
     * @return array|null
     */
    public function getCustomParameterList() {
        return $this->_customParameterList;
    }

    /**
     * @param array|null $customParameterList
     * @return $this
     */
    public function setCustomParameterList(array $customParameterList = null) {
        if (empty($customParameterList)) {
            $customParameterList = null;
        }
        $this->_customParameterList = $customParameterList;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFormat() {
        return $this->_format;
    }

    /**
     * @param string|null $format
     * @return $this
     */
    public function setFormat($format) {
        if (null !== $format) {
            $format = (string) $format;
        }
        $this->_format = $format;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * @param int|null $id
     * @return $this
     */
    public function setId($id) {
        if (null !== $id) {
            $id = (int) $id;
        }
        $this->_id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLabel() {
        return $this->_label;
    }

    /**
     * @param string|null $label
     * @return $this
     */
    public function setLabel($label) {
        if (null !== $label) {
            $label = (string) $label;
        }
        $this->_label = $label;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLanguage() {
        return $this->_language;
    }

    /**
     * @param string|null $language
     * @return $this
     */
    public function setLanguage($language) {
        if (null !== $language) {
            $language = (string) $language;
        }
        $this->_language = $language;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getRemarketingOnly() {
        return $this->_remarketingOnly;
    }

    /**
     * @param bool|null $remarketingOnly
     * @return $this
     */
    public function setRemarketingOnly($remarketingOnly) {
        if (null !== $remarketingOnly) {
            $remarketingOnly = (bool) $remarketingOnly;
        }
        $this->_remarketingOnly = $remarketingOnly;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray() {
        $conversion = [
            'google_conversion_id'       => $this->getId(),
            'google_conversion_language' => $this->getLanguage(),
            'google_conversion_format'   => $this->getFormat(),
            'google_conversion_color'    => $this->getColor(),
            'google_conversion_label'    => $this->getLabel(),
            'google_remarketing_only'    => $this->getRemarketingOnly(),
            'google_conversion_value'    => $this->getConversionValue(),
            'google_conversion_currency' => $this->getConversionCurrency(),
            'google_custom_params'       => $this->getCustomParameterList(),
        ];
        $conversion = array_filter($conversion, function ($value) {
            return null !== $value;
        });
        return $conversion;
    }

    /**
     * @return string
     */
    public function toJson() {
        $array = $this->toArray();
        return empty($array) ? '{}' : CM_Util::jsonEncode($array);
    }

    /**
     * @param array $conversionData
     * @return CMService_AdWords_Conversion
     */
    public static function fromArray(array $conversionData) {
        $conversion = new CMService_AdWords_Conversion();
        $conversion->setId(isset($conversionData['google_conversion_id']) ? $conversionData['google_conversion_id'] : null);
        $conversion->setLanguage(isset($conversionData['google_conversion_language']) ? $conversionData['google_conversion_language'] : null);
        $conversion->setFormat(isset($conversionData['google_conversion_format']) ? $conversionData['google_conversion_format'] : null);
        $conversion->setColor(isset($conversionData['google_conversion_color']) ? $conversionData['google_conversion_color'] : null);
        $conversion->setLabel(isset($conversionData['google_conversion_label']) ? $conversionData['google_conversion_label'] : null);
        $conversion->setRemarketingOnly(isset($conversionData['google_remarketing_only']) ? $conversionData['google_remarketing_only'] : null);
        $conversion->setConversionValue(isset($conversionData['google_conversion_value']) ? $conversionData['google_conversion_value'] : null);
        $conversion->setConversionCurrency(isset($conversionData['google_conversion_currency']) ? $conversionData['google_conversion_currency'] : null);
        $conversion->setCustomParameterList(isset($conversionData['google_custom_params']) ? $conversionData['google_custom_params'] : null);
        return $conversion;
    }

    /**
     * @param string $conversionJson
     * @return CMService_AdWords_Conversion
     */
    public static function fromJson($conversionJson) {
        $conversionJson = (string) $conversionJson;
        return self::fromArray(CM_Util::jsonDecode($conversionJson));
    }
}
