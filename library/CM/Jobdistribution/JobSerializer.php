<?php

class CM_Jobdistribution_JobSerializer {

    /** @var CM_Serializer_SerializerInterface */
    private $_serializer;

    /**
     * @param CM_Serializer_SerializerInterface $serializer
     */
    public function __construct(CM_Serializer_SerializerInterface $serializer) {
        $this->_serializer = $serializer;
    }

    /**
     * @param CM_Jobdistribution_Job_Abstract $job
     * @return string
     */
    public function serialize(CM_Jobdistribution_Job_Abstract $job) {
        $this->_verifyParams($job->getParams()->getParamsDecoded());
        $workloadParams = [
            'jobClassName' => get_class($job),
            'jobParams'    => $job->getParams()->getParamsEncoded(),
        ];
        return $this->_serializer->serialize($workloadParams);
    }

    /**
     * @param mixed $data
     * @return CM_Jobdistribution_Job_Abstract
     * @throws CM_Exception_Invalid
     * @throws CM_Exception_Nonexistent
     */
    public function unserialize($data) {
        try {
            $workloadParams = $this->_serializer->unserialize($data);
        } catch (CM_Exception_Nonexistent $ex) {
            throw new CM_Exception_Nonexistent("Cannot decode workload `{$ex->getMessage()}`, workload: `${data}'", null, null, CM_Exception::WARN);
        }
        $jobClassName = $workloadParams['jobClassName'];
        $params = $workloadParams['jobParams'];
        if (!is_subclass_of($jobClassName, CM_Jobdistribution_Job_Abstract::class, true)) {
            throw new CM_Exception_Invalid('Not valid job class', null, ['className' => $jobClassName]);
        }
        return new $jobClassName($params);
    }

    /**
     * @param mixed $value
     * @throws CM_Exception_InvalidParam
     */
    protected function _verifyParams($value) {
        if (is_array($value)) {
            \Functional\each($value, function ($value) {
                $this->_verifyParams($value);
            });
        }
        if (is_object($value) && !$value instanceof CM_ArrayConvertible) {
            throw new CM_Exception_InvalidParam('Object of class `' . get_class($value) . '` is not an instance of CM_ArrayConvertible');
        }
    }
}
