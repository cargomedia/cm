<?php

namespace CM\Url;

class AppUrl extends Url {

    /**
     * @return null|string
     */
    public function getLanguageSegment() {
        $language = $this->getLanguage();
        return $language ? 'language-' . $language->getAbbreviation() : null;
    }

    /**
     * @return null|string
     */
    public function getSiteSegment() {
        $site = $this->getSite();
        return $site ? 'site-' . $site->getId() : null;
    }

    /**
     * @return string|null
     */
    public function getDeployVersionSegment() {
        $version = $this->getDeployVersion();
        return $version ? 'version-' . $version : null;
    }
}
