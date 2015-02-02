<?php

class CM_Usertext_Filter_Emoticon_EscapeMarkdown extends CM_Usertext_Filter_Abstract {

    public function transform($text, CM_Frontend_Render $render) {
        $text = (string) $text;
        $text = preg_replace_callback('#:[[:alnum:]_]{1,50}:#u', function ($matches) {
            return str_replace('_', '-', $matches[0]);
        }, $text);
        $additionalMapping = $this->_getEmoticonAdditionalCodesMapping();
        $text = str_replace(array_keys($additionalMapping), array_values($additionalMapping), $text);
        return $text;
    }

    /**
     * @return array
     */
    private function _getEmoticonAdditionalCodesMapping() {
        $paging = new CM_Paging_Emoticon_All();
        $emoticonList = $paging->getItems();

        $filteredEmoticonList = \Functional\filter($emoticonList, function ($emoticon) {
            /** @var CM_Emoticon $emoticon */
            return count($emoticon->getCodes()) > 1;
        });

        $mapping = [];
        /** @var CM_Emoticon $filteredEmoticon */
        foreach ($filteredEmoticonList as $filteredEmoticon) {
            $codeList = $filteredEmoticon->getCodes();
            $defaultCode = $filteredEmoticon->getDefaultCode();
            foreach ($codeList as $code) {
                if ($code !== $defaultCode) {
                    $mapping[$code] = $defaultCode;
                }
            }
        }

        return $mapping;
    }
}
