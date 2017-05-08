<?php

namespace CM\Url;

class PageUrl extends AppUrl {

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
}
