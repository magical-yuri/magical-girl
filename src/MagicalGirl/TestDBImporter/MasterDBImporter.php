<?php

namespace MagicalGirl\TestDBImporter;

require_once(__DIR__ . '/../../../../../../lib/DB/DBConnections.php');

class MasterDBImporter
{
    /**
     * import
     *
     * @param string $tsvDirPath
     * @access public
     * @return void
     */
    public function import($tsvDirPath = null)
    {
        if (is_null($tsvDirPath)) {
          $tsvDirPath = realpath(__DIR__ . '/../../../../../../masterData');
        }

        $iterator = new \RecursiveDirectoryIterator($tsvDirPath);

        foreach ($iterator as $file) {
            if (!$iterator->hasChildren()) {
                continue;
            }

            $databaseName = $file->getFileName();
            $con = $this->getConnection($databaseName);
            $con->exec('set foreign_key_checks = 0');
            $this->importFromTsvInDir($iterator->getChildren(), $con);
            $con->exec('set foreign_key_checks = 1');
        }
    }

    protected function getConnection($databaseName)
    {
        return \DBConnections::getConnection($databaseName);
    }
        
    protected function importFromTsvInDir(\RecursiveDirectoryIterator $iterator, \PDO $con)
    {
        foreach ($iterator as $file) {
            $filePath = $file->getPathname();

            $tableName = $this->getTableNameFromFilePath($filePath);
            if (pathinfo($filePath, PATHINFO_EXTENSION) != 'tsv' or !$this->tableExists($tableName, $con)) {
                continue;
            }

            $this->importFromTsv($filePath, $con);
        }
    }

    protected function importFromTsv($tsvFilePath, \PDO $con)
    {
        if (!is_readable($tsvFilePath)) {
            return false;
        }

        $tableName = $this->getTableNameFromFilePath($tsvFilePath);
        $con->exec('truncate table `' . $tableName . '`');
        $con->exec('load data local infile "' . $tsvFilePath . '" into table `' . $tableName . '`');
    }

    protected function tableExists($tableName, \PDO $con)
    {
        $stmt = $con->query("show tables like '{$tableName}'");
        $row = $stmt->fetch();
        $stmt->closeCursor();

        return (bool) $row;
    }

    protected function getTableNameFromFilePath($tsvFilePath)
    {
        return pathinfo($tsvFilePath, PATHINFO_FILENAME);
    }
}

