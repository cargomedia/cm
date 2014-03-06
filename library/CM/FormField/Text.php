<?php

class CM_FormField_Text extends CM_FormField_Abstract {

  /**
   * @param int|null     $lengthMin
   * @param int|null     $lengthMax
   * @param boolean|null $forbidBadwords
   */
  public function __construct($lengthMin = null, $lengthMax = null, $forbidBadwords = null) {
    $this->_options['lengthMin'] = isset($lengthMin) ? (int) $lengthMin : null;
    $this->_options['lengthMax'] = isset($lengthMax) ? (int) $lengthMax : null;
    $this->_options['forbidBadwords'] = (boolean) $forbidBadwords;
  }

  public function validate($userInput, CM_Response_Abstract $response) {
    if (isset($this->_options['lengthMax']) && mb_strlen($userInput) > $this->_options['lengthMax']) {
      throw new CM_Exception_FormFieldValidation('Too long');
    }
    if (isset($this->_options['lengthMin']) && mb_strlen($userInput) < $this->_options['lengthMin']) {
      throw new CM_Exception_FormFieldValidation('Too short');
    }
    if (!empty($this->_options['forbidBadwords'])) {
      $badwordList = new CM_Paging_ContentList_Badwords();
      if ($badword = $badwordList->getMatch($userInput)) {
        throw new CM_Exception_FormFieldValidation('The word `{$badword}` is not allowed', array('badword' => $badword));
      }
    }
    return $userInput;
  }

  public function prepare(array $params) {
    $this->setTplParam('autocorrect', isset($params['autocorrect']) ? $params['autocorrect'] : null);
    $this->setTplParam('autocapitalize', isset($params['autocapitalize']) ? $params['autocapitalize'] : null);
    $this->setTplParam('tabindex', isset($params['tabindex']) ? $params['tabindex'] : null);
    $this->setTplParam('class', isset($params['class']) ? $params['class'] : null);
    $this->setTplParam('placeholder', isset($params['placeholder']) ? $params['placeholder'] : null);
  }
}
