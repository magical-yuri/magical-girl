<?php

namespace MagicalGirl\DBConnections;

abstract class DBConnectionsAbstract
{
    private static $CONNECTIONS = array();

    public static function getConnection($databaseName)
    {
        if (!array_key_exists($databaseName, self::$CONNECTIONS)) {
            $methodNamePrefix = 'get' . ucfirst($databaseName);

            $host = self::checkAndExecStaticMethod($methodNamePrefix . 'Host');
            $userName = self::checkAndExecStaticMethod($methodNamePrefix . 'UserName');
            $password = self::checkAndExecStaticMethod($methodNamePrefix . 'Password');

            self::$CONNECTIONS[$databaseName] = new \PDO(
              "mysql:dbname=$databaseName;host=$host",
              $userName,
              $password,
              array(\PDO::MYSQL_ATTR_LOCAL_INFILE => true)
            );
        }

        return self::$CONNECTIONS[$databaseName];
    }

    private static function checkAndExecStaticMethod($methodName)
    {
        if ( !method_exists('DBConnections', $methodName) ) {
            $dirPath = realpath(__DIR__ . '/../../../../../../lib/DB');
            throw new \Exception("You need to implement method to get PDO that named '$methodName' on 'DBConnections' class in '$dirPath/DBConnections.php'");
        }
        return static::$methodName();
    }
}
