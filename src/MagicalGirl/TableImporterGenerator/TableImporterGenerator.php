<?php

namespace MagicalGirl\TableImporterGenerator;

require_once(__DIR__ . '/../../../../../../lib/DB/DBConnections.php');
use MagicalGirl\Component\DB\QueryResult;

class TableImporterGenerator
{
    protected static $PARENT_CLASS = 'TableImporter';
    protected static $RANDOM_VALUE_GENERATOR_CLASS = 'RandomValueGenerator';
    protected static $CLASS_NAME_BASE = '%databaseName%%tableName%Importer';
    protected static $BASE_CLASS_DIR = 'base';
    protected static $BASE_CLASS_PREFIX = 'Base';
    protected static $TABSTR = '    ';

    protected static $MYSQL_TYPE_INT = array(
        'tinyint',
        'smallint',
        'mediumint',
        'int',
        'bigint',
    );
    protected static $MYSQL_TYPE_FLOAT = array(
        'float',
        'double',
    );
    protected static $MYSQL_TYPE_DATE = array(
        'date',
    );
    protected static $MYSQL_TYPE_DATETIME = array(
        'datetime',
        'timestamp',
    );
    protected static $MYSQL_TYPE_STRING = array(
        'char',
        'varchar',
        'binary',
        'varbinary',
    );
    protected static $MYSQL_TYPE_MULTILINE_TEXT = array(
        'tinyblob',
        'blob',
        'mediumblob',
        'longblog',
        'tinytext',
        'text',
        'mediumtext',
        'longtext',
    );
    protected static $MYSQL_TYPE_LIST = array(
        'set',
        'enum',
    );

    protected $databaseName;
    protected $tableName;
    protected $dirPath;
    protected $className;
    protected $baseClassName;
    protected $baseClassDir;
    protected $con;

    public function __construct($databaseName, $tableName, $dirPath)
    {
        $this->databaseName = $databaseName;
        $this->tableName = $tableName;
        $this->dirPath = $dirPath;
        $this->className = $this->getClassName($databaseName, $tableName);
        $this->baseClassName = self::$BASE_CLASS_PREFIX . $this->className;
        $this->baseClassDir = self::$BASE_CLASS_DIR;
        $this->con = \DBConnections::getConnection($this->getDatabaseName());
    }

    public function generate()
    {
        $desc = $this->getDescription();
        $autoIncrementField;
        $fields = array();
        $pkeys = array();
        while ($row = $desc->getResultRow()) {
            $fields[$row['Field']] = $row;

            if ($row['Key'] == 'PRI') {
                $pkeys[] = $row['Field'];
            }

            if ($row['Extra'] == 'auto_increment') {
                $autoincrementField = $row['Field'];
            }
        }

        $filePaths = array(
            'base'     => $this->getBaseTargetFilePath(),
            'concrete' => $this->getTargetFilePath()
        );
        $fh = fopen($filePaths['base'], 'w');

        ob_start();
        include('TableImporterSkeletonBase.php');
        fputs($fh, ob_get_contents());
        fclose($fh);
        ob_clean();

        if (!file_exists($filePaths['concrete'])) {
            $fh = fopen($filePaths['concrete'], 'w');

            ob_start();
            include('TableImporterSkeleton.php');
            fputs($fh, ob_get_contents());
            fclose($fh);
            ob_clean();
        }

        return $filePaths;
    }

    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    protected function getBaseTargetFilePath()
    {
        $targetDirPath = realpath($this->getTargetDirPath(DIRECTORY_SEPARATOR . self::$BASE_CLASS_DIR));
        return $targetDirPath . DIRECTORY_SEPARATOR . $this->baseClassName . '.php';
    }

    protected function getTargetFilePath()
    {
        $targetDirPath = realpath($this->getTargetDirPath());
        return $targetDirPath . DIRECTORY_SEPARATOR . $this->className . '.php';
    }

    protected function getTargetDirPath($relativeDirPath = '')
    {
        $this->findOrCreateDirectory($this->dirPath);
        $dbDirPath = $this->dirPath . DIRECTORY_SEPARATOR . $this->databaseName;
        $this->findOrCreateDirectory($dbDirPath);
        $targetDirPath = $dbDirPath . $relativeDirPath;
        $this->findOrCreateDirectory($targetDirPath);

        return $targetDirPath;
    }

    protected function getDescription()
    {
        $sql = "desc `{$this->tableName}`";

        try {
            $stmt = $this->con->query($sql);
            if ($stmt == false) {
                $error = $this->con->errorInfo();
                throw new \Exception($error[2]);
            }
            $result = new QueryResult($stmt);
        } catch (\PDOException $e) {
            echo $e->getMessage();
            exit;
        }

        return $result;
    }

    protected function getClassName($databaseName, $tableName)
    {
        $className = self::$CLASS_NAME_BASE;
        $className = str_replace('%databaseName%', $this->camelize($databaseName), $className);
        $className = str_replace('%tableName%', $this->camelize($tableName), $className);
        return $className;
    }

    protected function getRandomValueGenerateMethod($type, $key)
    {
        $type = split('\(', $type);
        $type[0] = split(' ', $type[0]);
        $type[0] = $type[0][0];
        $type = array_map(function ($row) { return str_replace(')', '', $row); }, $type);

        $isUnique = false;
        if ($key == 'PRI' or $key == 'UNI') {
            $isUnique = true;
        }

        if (in_array($type[0], self::$MYSQL_TYPE_INT)) {
            $method = self::$RANDOM_VALUE_GENERATOR_CLASS . '::getRandomInt(0, ' . str_repeat('9', (int) $type[1]) . ')';
        } elseif (in_array($type[0], self::$MYSQL_TYPE_STRING)) {
            $method = self::$RANDOM_VALUE_GENERATOR_CLASS . '::getRandomString(' . $type[1] . ', array(' . self::$RANDOM_VALUE_GENERATOR_CLASS . '::LETTER_TYPE_LOWER_CASE)';
            if ($isUnique) {
                $method .= ', 1';
            }
            $method .= ')';
        } elseif (in_array($type[0], self::$MYSQL_TYPE_DATE)) {
            $method = self::$RANDOM_VALUE_GENERATOR_CLASS . '::getRandomDate(time() - 60 * 60 * 24 * 365, time())';
        } elseif (in_array($type[0], self::$MYSQL_TYPE_DATETIME)) {
            $method = self::$RANDOM_VALUE_GENERATOR_CLASS . '::getRandomDate(time() - 60 * 60 * 24 * 365, time())';
        } elseif (in_array($type[0], self::$MYSQL_TYPE_FLOAT)) {
            $method = self::$RANDOM_VALUE_GENERATOR_CLASS . '::getRandomFloat(0, 1000)';
        } elseif (in_array($type[0], self::$MYSQL_TYPE_MULTILINE_TEXT)) {
            $method = self::$RANDOM_VALUE_GENERATOR_CLASS . '::getRandomMultilineText(20, 10, array(' . self::$RANDOM_VALUE_GENERATOR_CLASS . '::LETTER_TYPE_LOWER_CASE))';
        } elseif (in_array($type[0], self::$MYSQL_TYPE_LIST)) {
            array_shift($type);
            $method = self::$RANDOM_VALUE_GENERATOR_CLASS . '::getRandomList(array(' . implode($type) . '))';
        } else {
            throw new \Exception('データ型' . $type[0] . 'に対応する値ジェネレータが定義されていません');
        }

        return $method;
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

    protected function getStringArrayElements(array $array, $tabcnt = 0)
    {
        return "'" . implode("',\n" . str_repeat(self::$TABSTR, $tabcnt) . "'", $array) . "'";
    }

    private function findOrCreateDirectory($dirPath)
    {
        if (!file_exists($dirPath)) {
            mkdir($dirPath);
        }

        if (!is_dir($dirPath)) {
            throw new \Exception($dirPath . ' is not directory.');
        }
    }
}
