<?php

namespace Ez\DbLinker\Driver\Connection;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\DriverManager;
use Ez\DbLinker\RetryStrategy;

class RetryConnection implements Connection
{
    private $wrappedConnectionParams;
    private $wrappedConnection;
    private $retryStrategy;
    private $transactionLevel = 0;

    use CallAndRetry;

    public function __construct(Array $wrappedConnectionParams, RetryStrategy $retryStrategy)
    {
        $this->wrappedConnectionParams = $wrappedConnectionParams;
        $this->retryStrategy = $retryStrategy;
    }

    /**
     * Prepares a statement for execution and returns a Statement object.
     *
     * @param string $prepareString
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function prepare($prepareString) {
        return new RetryStatement(
            $this->callAndRetry(__FUNCTION__, func_get_args()),
            $this,
            $this->retryStrategy
        );
    }

    /**
     * Executes an SQL statement, returning a result set as a Statement object.
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function query() {
        return $this->callAndRetry(__FUNCTION__, func_get_args());
    }

    /**
     * Quotes a string for use in a query.
     *
     * @param string  $input
     * @param integer $type
     *
     * @return string
     */
    public function quote($input, $type = \PDO::PARAM_STR) {
        return $this->callAndRetry(__FUNCTION__, func_get_args());
    }

    /**
     * Executes an SQL statement and return the number of affected rows.
     *
     * @param string $statement
     *
     * @return integer
     */
    public function exec($statement) {
        return $this->callAndRetry(__FUNCTION__, func_get_args());
    }

    /**
     * Returns the ID of the last inserted row or sequence value.
     *
     * @param string|null $name
     *
     * @return string
     */
    public function lastInsertId($name = null) {
        return $this->callAndRetry(__FUNCTION__, func_get_args());
    }

    /**
     * Initiates a transaction.
     *
     * @return boolean TRUE on success or FALSE on failure.
     */
    public function beginTransaction() {
        $this->transactionLevel++;
        return $this->callAndRetry(__FUNCTION__, func_get_args());
    }

    /**
     * Commits a transaction.
     *
     * @return boolean TRUE on success or FALSE on failure.
     */
    public function commit() {
        $this->transactionLevel--;
        return $this->callAndRetry(__FUNCTION__, func_get_args());
    }

    /**
     * Rolls back the current transaction, as initiated by beginTransaction().
     *
     * @return boolean TRUE on success or FALSE on failure.
     */
    public function rollBack() {
        $this->transactionLevel--;
        return $this->callAndRetry(__FUNCTION__, func_get_args());
    }

    /**
     * Returns the error code associated with the last operation on the database handle.
     *
     * @return string|null The error code, or null if no operation has been run on the database handle.
     */
    public function errorCode() {
        return $this->callAndRetry(__FUNCTION__, func_get_args());
    }

    /**
     * Returns extended error information associated with the last operation on the database handle.
     *
     * @return array
     */
    public function errorInfo() {
        return $this->callAndRetry(__FUNCTION__, func_get_args());
    }

    public function close()
    {
        if ($this->wrappedConnection !== null) {
            $this->wrappedConnection->close();
            $this->wrappedConnection = null;
        }
    }

    public function transactionLevel()
    {
        return $this->transactionLevel;
    }

    private function wrappedObject()
    {
        return $this->wrappedConnection();
    }

    public function wrappedConnection()
    {
        if ($this->wrappedConnection === null) {
            $this->wrappedConnection = DriverManager::getConnection($this->wrappedConnectionParams);
        }
        return $this->wrappedConnection;
    }

    public function retryStrategy()
    {
        return $this->retryStrategy;
    }

    public function retryConnection()
    {
        return $this;
    }
}
