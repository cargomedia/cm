<?php

class CM_Site_SiteFactory {

    /** @var CM_Site_Abstract[] */
    private $_siteList;

    /** @var CM_Site_Abstract[]|null */
    private $_siteListSorted = null;

    /**
     * @param CM_Site_Abstract[]|null $siteList
     */
    public function __construct(array $siteList = null) {
        $this->_siteList = $siteList;
    }

    /**
     * @param CM_Http_Request_Abstract $request
     * @return CM_Site_Abstract|null
     */
    public function findSite(CM_Http_Request_Abstract $request) {
        $request = clone $request;
        foreach ($this->_getSiteListSorted() as $site) {
            if ($site->match($request)) {
                return $site;
            }
        }
        return null;
    }

    /**
     * @param CM_Http_Request_Abstract $request
     * @return CM_Site_Abstract
     * @throws CM_Exception
     */
    public function getSite(CM_Http_Request_Abstract $request) {
        $site = $this->findSite($request);
        if (null === $site) {
            throw new CM_Exception('No suitable site found for request.', null, ['request' => $request]);
        }
        return $site;
    }

    /**
     * @param string $id
     * @return CM_Site_Abstract|null
     */
    public function findSiteById($id) {
        $id = (string) $id;
        return \Functional\first($this->_getSiteList(), function (CM_Site_Abstract $site) use ($id) {
            return $site->getId() === $id;
        });
    }

    /**
     * @param string $id
     * @return CM_Site_Abstract
     * @throws CM_Exception_Invalid
     */
    public function getSiteById($id) {
        $id = (string) $id;
        $site = $this->findSiteById($id);
        if (null === $site) {
            throw new CM_Exception_Invalid('Site is not found', null, ['siteId' => $id]);
        }
        return $site;
    }

    /**
     * These 2 method were temporary added till we resolve ambiguity related to splitting siteId and type
     * especially for models where type is currently stored.
     *
     * @param int $type
     * @return CM_Site_Abstract|null
     * @deprecated use site `id` when it's possible
     */
    public function findSiteByType($type) {
        $type = (int) $type;
        return \Functional\first($this->_getSiteList(), function (CM_Site_Abstract $site) use ($type) {
            return $site->getType() === $type;
        });
    }

    /**
     * @param int $type
     * @return CM_Site_Abstract
     * @throws CM_Exception_Invalid
     * @deprecated use binding by id, in the future site `type` will be not unique
     */
    public function getSiteByType($type) {
        $type = (int) $type;
        $site = $this->findSiteByType($type);
        if (null === $site) {
            throw new CM_Exception_Invalid('Site is not found', null, ['type' => $type]);
        }
        return $site;
    }

    /**
     * @return CM_Site_Abstract
     * @throws CM_Exception_Invalid
     */
    public function getDefaultSite() {
        $defaultSite = \Functional\first($this->_getSiteList(), function (CM_Site_Abstract $site) {
            return true === $site->getDefault();
        });
        if (null === $defaultSite) {
            throw new CM_Exception_Invalid('Default site is not set');
        }
        return $defaultSite;
    }

    /**
     * @return CM_Site_Abstract[]
     */
    protected function _getSiteList() {
        if (null === $this->_siteList) {
            $this->_siteList = (new CM_Paging_Site_All())->getItems();
        }
        return $this->_siteList;
    }

    /**
     * @return CM_Site_Abstract[]
     */
    protected function _getSiteListSorted() {
        if (null === $this->_siteListSorted) {
            $siteList = $this->_getSiteList();
            usort($siteList, function (CM_Site_Abstract $site1, CM_Site_Abstract $site2) {
                $length1 = mb_strlen($site1->getUrlString());
                $length2 = mb_strlen($site2->getUrlString());
                if ($length1 == $length2) {
                    return 0;
                }
                return $length1 > $length2 ? -1 : 1;
            });
            $this->_siteListSorted = $siteList;
        }
        return $this->_siteListSorted;
    }
}
