<?php

class CM_Db_Statement {

    /** @var PDOStatement */
    private $_pdoStatement;

    /** @var CM_Db_Client */
    private $_client;

    /**
     * @param PDOStatement $pdoStatement
     * @param CM_Db_Client $client
     */
    public function __construct(PDOStatement $pdoStatement, CM_Db_Client $client) {
        $this->_pdoStatement = $pdoStatement;
        $this->_client = $client;
    }

    /**
     * @param array|null $parameters
     * @param bool|null  $disableQueryBuffering
     * @throws CM_Db_Exception
     * @return CM_Db_Result
     */
    public function execute(array $parameters = null, $disableQueryBuffering = null) {
        $disableQueryBuffering = (bool) $disableQueryBuffering;
        $retryCount = 1;
        for ($try = 0; true; $try++) {
            try {
                if ($disableQueryBuffering) {
                    $this->_client->setBuffered(false);
                }
                @$this->_pdoStatement->execute($parameters);
                if ($disableQueryBuffering) {
                    $this->_client->setBuffered(true);
                }
                CM_Service_Manager::getInstance()->getDebug()->incStats('mysql', $this->getQueryString());
                return new CM_Db_Result($this->_pdoStatement);
            } catch (PDOException $e) {
                if ($try < $retryCount && $this->_client->isConnectionLossError($e)) {
                    $this->_client->disconnect();
                    $this->_client->connect();
                    $this->_reCreatePdoStatement();
                    continue;
                }
                throw new CM_Db_Exception('Cannot execute SQL statement', null, [
                    'tries'                    => $try,
                    'originalExceptionMessage' => $e->getMessage(),
                    'query'                    => $this->_pdoStatement->queryString,
                    'parameters'               => $parameters,
                ]);
            }
        }
        throw new CM_Db_Exception('Line should never be reached');
    }

    /**
     * @return string
     */
    public function getQueryString() {
        return $this->_pdoStatement->queryString;
    }

    private function _reCreatePdoStatement() {
        $this->_pdoStatement = $this->_client->createPdoStatement($this->getQueryString());
    }
}
