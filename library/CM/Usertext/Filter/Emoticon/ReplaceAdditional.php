<?php

class CM_Usertext_Filter_Emoticon_ReplaceAdditional extends CM_Usertext_Filter_Abstract {

    public function transform($text, CM_Frontend_Render $render) {
        $text = (string) $text;
        $additionalCodesMapping = $this->_getEmoticonAdditionalCodesMapping();
        $text = str_replace(array_keys($additionalCodesMapping), array_values($additionalCodesMapping), $text);
        return $text;
    }

    /**
     * @return array
     */
    private function _getEmoticonAdditionalCodesMapping() {
        $paging = new CM_Paging_Emoticon_All();
        $emoticonList = $paging->getItems();

        $filteredEmoticonList = \Functional\filter($emoticonList, function (CM_Emoticon $emoticon) {
            return count($emoticon->getCodes()) > 1;
        });

        $mapping = [];
        /** @var CM_Emoticon $emoticon */
        foreach ($filteredEmoticonList as $emoticon) {
            $codeList = $emoticon->getCodes();
            $defaultCode = $emoticon->getDefaultCode();
            foreach ($codeList as $code) {
                if ($code !== $defaultCode) {
                    $mapping[$code] = $defaultCode;
                }
            }
        }

        return $mapping;
    }
}
