<?php

class CM_Asset_Css_File extends CM_Asset_Css {

    /**
     * @param CM_Frontend_Render $render
     * @param CM_File            $fileLess
     * @param bool|null          $addVariables
     */
    public function __construct(CM_Frontend_Render $render, CM_File $fileLess, $addVariables = null) {
        parent::__construct($render);

        if ($addVariables) {
            $this->_addVariables($render);
        }
        $this->add($fileLess->read());
    }
}
