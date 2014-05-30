<?php

class CM_Component_LogList extends CM_Component_Abstract {

    public function checkAccessible(CM_Render $render) {
        if (!CM_Bootloader::getInstance()->isDebug()) {
            throw new CM_Exception_NotAllowed();
        }
    }

    public function prepare() {
        $type = $this->_params->getInt('type');
        $aggregate = $this->_params->has('aggregate') ? $this->_params->getInt('aggregate') : null;
        $urlPage = $this->_params->has('urlPage') ? $this->_params->getString('urlPage') : null;
        $urlParams = $this->_params->has('urlParams') ? $this->_params->getArray('urlParams') : null;

        $aggregationPeriod = $aggregate;
        if (0 === $aggregationPeriod) {
            $deployStamp = CM_App::getInstance()->getDeployVersion();
            $aggregationPeriod = time() - $deployStamp;
        }
        $logList = CM_Paging_Log_Abstract::factory($type, (bool) $aggregationPeriod, $aggregationPeriod);
        $logList->setPage($this->_params->getPage(), $this->_params->getInt('count', 50));

        $this->setTplParam('type', $type);
        $this->setTplParam('logList', $logList);
        $this->setTplParam('aggregate', $aggregate);
        $this->setTplParam('aggregationPeriod', $aggregationPeriod);
        $this->setTplParam('aggregationPeriodList', array(3600, 86400, 7 * 86400, 31 * 86400));
        $this->setTplParam('urlPage', $urlPage);
        $this->setTplParam('urlParams', $urlParams);

        $this->_setJsParam('type', $type);
    }

    public static function ajax_flushLog(CM_Params $params, CM_ComponentFrontendHandler $handler, CM_Response_View_Ajax $response) {
        if (!static::_getAllowedFlush($response->getRender())) {
            throw new CM_Exception_NotAllowed();
        }

        $type = $params->getInt('type');
        $logList = CM_Paging_Log_Abstract::factory($type);
        $logList->flush();

        $response->reloadComponent();
    }

    /**
     * @param CM_Render $render
     * @return bool
     */
    protected static function _getAllowedFlush(CM_Render $render) {
        return CM_Bootloader::getInstance()->isDebug();
    }
}
