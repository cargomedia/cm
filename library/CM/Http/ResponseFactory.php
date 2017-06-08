<?php

class CM_Http_ResponseFactory implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var string[] */
    private $_responseClassList;

    /**
     * @param CM_Service_Manager $serviceManager
     * @param string[]|null      $responseClassList
     */
    public function __construct(CM_Service_Manager $serviceManager, array $responseClassList = null) {
        $this->setServiceManager($serviceManager);
        if (null === $responseClassList) {
            $responseClassList = array_reverse(CM_Http_Response_Abstract::getClassChildren());
        }

        /** @var $responseClass CM_Http_Response_Abstract */
        foreach ($responseClassList as $index => $responseClass) {
            if ($responseClass::catchAll()) {
                unset($responseClassList[$index]);
                $responseClassList[] = $responseClass;
            }
        }

        $this->_responseClassList = $responseClassList;
    }

    /**
     * @param CM_Http_Request_Abstract $request
     * @return CM_Http_Response_Abstract|null
     */
    public function findResponse(CM_Http_Request_Abstract $request) {
        $request = clone $request;
        /** @var $responseClass CM_Http_Response_Abstract */
        foreach ($this->_responseClassList as $responseClass) {
            if ($response = $responseClass::createFromRequest($request, $request->getSite(), $this->getServiceManager())) {
                return $response;
            }
        }
        return null;
    }

    /**
     * @param CM_Http_Request_Abstract $request
     * @return CM_Http_Response_Abstract
     * @throws CM_Exception
     */
    public function getResponse(CM_Http_Request_Abstract $request) {
        $response = $this->findResponse($request);
        if (null === $response) {
            throw new CM_Exception('No suitable response found for request.', null, ['request' => $request]);
        }
        return $response;
    }

}
