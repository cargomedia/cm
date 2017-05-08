<?php

namespace CM\Url;

use CM_Model_Language;

class PageUrl extends AppUrl {

    public function parseLanguage() {
        $language = null;
        $segments = $this->getSegments();
        if (0 < count($segments)) {
            $language = CM_Model_Language::findByAbbreviation($segments[0]);
            if ($language) {
                $this->_dropPathSegment($segments[0]);
            }
        }
        return $language ? $this->withLanguage($language) : $this;
    }

    public function getLanguageSegment() {
        $language = $this->getLanguage();
        return $language ? $language->getAbbreviation() : null;
    }

    public function getSiteSegment() {
        return null;
    }

    public function getDeployVersionSegment() {
        return null;
    }

    public static function matchUri($uri) {
        return true;
    }
}
