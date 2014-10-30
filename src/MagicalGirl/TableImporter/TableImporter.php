<?php

namespace MagicalGirl\TableImporter;

require_once(__DIR__ . '/../../../../../../lib/DB/DBConnections.php');
use MagicalGirl\TableImporter\Exception\TableAddRowException;
use MagicalGirl\Component\DB\QueryParameterHolder;
use MagicalGirl\Component\DB\QueryResult;

class TableImporter
{
    const OPTION_WITHOUT_TRUNCATE = 0;
    const OPTION_WITH_TRUNCATE = 1;

    protected static $DATABASE_NAME;
    protected static $TABLE_NAME;
    protected static $PRIMARY_KEY = array();
    protected static $AUTO_INCREMENT_FIELD; 
    protected static $FIELDS = array();

    protected static $MAX_RETRY_COUNT = 10;
    protected static $ARRAY_STRING_SEPARATOR = ',';

    protected $con;

    public function __construct($option = null)
    {
        $this->con = \DBConnections::getConnection(self::getDatabasename());
        if (is_null($option)) {
            $option = self::OPTION_WITH_TRUNCATE;
        }

        if ($option == self::OPTION_WITHOUT_TRUNCATE) {
            return ;
        } 
        
        $this->con->exec('set foreign_key_checks = 0');
        $this->truncate();
        $this->con->exec('set foreign_key_checks = 1');
    }

    public static function getDatabaseName()
    {
        return static::$DATABASE_NAME;
    }

    public static function getTableName()
    {
        return static::$TABLE_NAME;
    }

    public static function getFields()
    {
        return static::$FIELDS;
    }

    /**
     * addRow 
     * 
     * @param array $values array(fieldName => value, ...) MagicalGirl will insert these values in these columns.
     * @param array $ngValues array(fieldName => array(value, value, ...)) MagicalGirl will never insert these values in these columns.
     * @access public
     * @return array $row array(fieldName => value, ...) actually inserted values
     */
    public function addRow(array $values = array(), array $ngValues = array())
    {
        $insertValues = $this->getRandomValues($values, $ngValues);
        $result = $this->insert($insertValues);

        $retryCount = 0;
        while (!$result->isSuccess()) {
            if ($retryCount >= self::$MAX_RETRY_COUNT) {
                throw new TableAddRowException(
                    'insert failed count is exceeded ' . self::$MAX_RETRY_COUNT . ' of max retry count'
                );
            }

            throw new TableAddRowException($result->getErrorMessage());

            $method = $this->getValueGenerateMethod($duplicateField);
            $insertValues[$duplicateField] = $this->$method();
            $result = $this->insert($insertValues);
            $retryCount++;
        }

        $pkey = $result->getPkey();
        $result = $this->selectByColumnValue($pkey);

        return $result->getResultRow();
    }

    protected function getRandomValues(array $values, array $ngValues)
    {
        foreach (static::$FIELDS as $field) {
            if (!array_key_exists($field, $values) and $field != static::$AUTO_INCREMENT_FIELD) {
                $method = $this->getValueGenerateMethod($field);

                do {
                    $values[$field] = $this->$method();
                } while (array_key_exists($field, $ngValues) and in_array($values[$field], $ngValues[$field]));
            }
        }

        return $values;
    }

    protected function insert(array $values)
    {
        $tableName = static::$TABLE_NAME;
        $queryParam = new QueryParameterHolder($values);

        $sql = <<<EOT
            insert into {$tableName} ( 
                {$this->getArrayString($queryParam->getFieldStrings())}
            ) values (
                {$this->getArrayString($queryParam->getPlaceholders())}
            )
EOT;

        return $this->executeQuery($sql, $queryParam);
    }

    protected function selectByColumnValue(array $values)
    {
        $tableName = static::$TABLE_NAME;

        $queryParam = new QueryParameterHolder($values);

        $sql = <<<EOT
            select * from `{$tableName}`
            where 
EOT;
        $andArray = array();
        foreach (array_keys($values) as $field) {
            $andArray[] = "`{$field}` = {$queryParam->getPlaceholder($field)}";
        }

        $sql .= implode(' and ', $andArray);

        return $this->executeQuery($sql, $queryParam);
    }

    protected function truncate()
    {
        $tableName = static::$TABLE_NAME;
        $sql = 'truncate table `' . static::$TABLE_NAME . '`';
    
        return $this->executeQuery($sql);
    }

    protected function executeQuery($sql, QueryParameterHolder $queryParam = null)
    {
        if (!isset($queryParam)) {
            $queryParam = new QueryParameterHolder();
        }

        try{
            $stmt = $this->con->prepare($sql);
            $stmt->execute($queryParam->getParameterArray());
            $result = new QueryResult($stmt);

            foreach (static::$PRIMARY_KEY as $pkey) {
                $result->setPkey($pkey, $queryParam->getParameter($pkey));
            }

            if (isset(static::$AUTO_INCREMENT_FIELD)) {
                $result->setPkey(static::$AUTO_INCREMENT_FIELD, $this->con->lastInsertId());
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
            exit;
        }

        return $result;
    }

    protected function getValueGenerateMethod($field)
    {
        return 'get' . $this->camelize($field);
    }

    protected function camelize($str)
    {
        $str = str_replace('_', ' ', strtolower($str));
        $str = ucwords($str);
        return str_replace(' ', '', $str);
    }

    protected function getArrayString(array $array)
    {
        return implode(self::$ARRAY_STRING_SEPARATOR, $array);
    }

}
