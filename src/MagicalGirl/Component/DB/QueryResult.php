<?php

namespace MagicalGirl\Component\DB;

class QueryResult
{
    private static $MYSQL_ERROR_CODE_SUCCESS = '00000';
    private static $MYSQL_ERROR_CODE_DUPLICATE = '23000';

    private $stmt;
    private $pkey = array();

    public function __construct(\PDOStatement $stmt)
    {
        $this->stmt = $stmt;
    }

    public function setPkey($keyName, $pkey)
    {
        if ($this->isSuccess()) {
            $this->pkey[$keyName] = $pkey;
        }
    }

    public function getErrorMessage()
    {
        $errorInfo = $this->stmt->errorInfo();
        return $errorInfo[2];
    }

    public function getPkey()
    {
        return $this->pkey;
    }

    public function isSuccess()
    {
        return $this->stmt->errorCode() === self::$MYSQL_ERROR_CODE_SUCCESS;
    }

    public function isDuplicate()
    {
        if ($this->stmt->errorCode() != self::$MYSQL_ERROR_CODE_DUPLICATE) {
            return false;
        }

        return preg_match('/Duplicate entry \'.*?\' for key \'.*?\'/', $this->getErrorMessage());
    }

    public function getResultRow()
    {
        return $this->stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function __destruct()
    {
        $this->stmt->closeCursor();
    }
}
