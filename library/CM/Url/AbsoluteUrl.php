<?php

namespace CM\Url;

class AbsoluteUrl extends AbstractUrl {

    public function withEnvironment(\CM_Frontend_Environment $environment, array $options = null) {
        return $this->getRelativeUrl()->withEnvironment($environment, $options);
    }

    /**
     * @return RelativeUrl
     */
    public function getRelativeUrl() {
        $relativeAsString = $this->getPath();
        if ($query = $this->getQuery()) {
            $relativeAsString .= '?' . $query;
        }
        if ($fragment = $this->getFragment()) {
            $relativeAsString .= '#' . $fragment;
        }
        return RelativeUrl::createFromString($relativeAsString);
    }

    /**
     * @inheritdoc
     */
    protected static $supportedSchemes = [
        'http'  => 80,
        'https' => 443,
    ];

    /**
     * @inheritdoc
     */
    protected function isValid() {
        return $this->isValidGenericUri()
            && $this->isValidAbsoluteUri()
            && $this->isValidHierarchicalUri();
    }

    /**
     * @return bool
     */
    protected function isValidAbsoluteUri() {
        return '' !== $this->getScheme() && '' !== $this->getHost();
    }
}
