<?php

abstract class CM_FormField_Suggest extends CM_FormField_Abstract {

    /**
     * @param string    $term
     * @param array     $options
     * @param CM_Frontend_Render $render
     * @throws CM_Exception_NotImplemented
     * @return array list(list('id' => $id, 'name' => $name[, 'description' => $description, 'img' => $img, 'class' => string]))
     */
    protected function _getSuggestions($term, array $options, CM_Frontend_Render $render) {
        throw new CM_Exception_NotImplemented();
    }

    /**
     * @param mixed     $item
     * @param CM_Frontend_Render $render
     * @return array list('id' => $id, 'name' => $name[, 'description' => $description, 'img' => $img, 'class' => string])
     */
    abstract public function getSuggestion($item, CM_Frontend_Render $render);

    public function prepare(CM_Params $renderParams, CM_Frontend_ViewResponse $viewResponse) {
        $viewResponse->set('class', $renderParams->has('class') ? $renderParams->getString('class') : null);
        $viewResponse->set('placeholder', $renderParams->has('placeholder') ? $renderParams->getString('placeholder') : null);
    }

    public function validate($userInput, CM_Response_Abstract $response) {
        $values = explode(',', $userInput);
        $values = array_unique($values);
        if ($this->_options['cardinality'] && count($values) > $this->_options['cardinality']) {
            throw new CM_Exception_FormFieldValidation('Too many elements.');
        }
        return $values;
    }

    public function ajax_getSuggestions(CM_Params $params, CM_Frontend_JavascriptContainer_View $handler, CM_Response_View_Ajax $response) {
        $field = new static(null);
        $suggestions = $field->_getSuggestions($params->getString('term'), $params->getArray('options'), $response->getRender());
        return $suggestions;
    }

    protected function _setup() {
        $this->_options['cardinality'] = $this->_params->has('cardinality') ? $this->_params->getInt('cardinality') : null;
        $this->_options['enableChoiceCreate'] = $this->_params->getBoolean('enableChoiceCreate', false);
    }
}
