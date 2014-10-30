<?php

namespace Tests\MagicalGirl\TableImporterGenerator;

use MagicalGirl\TableImporter\TableImporter;
use MagicalGirl\TableImporterGenerator\TableImporterGenerator;

abstract class TableImporterGeneratorAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * getTargetPath 
     * 
     * @abstract
     * @access protected
     * @return string $targetPath
     */
    protected function getTargetPath()
    {
        return realpath(__DIR__ . '/../../../../../../test/tmp');
    }

    /**
     * testGenerateAndInsert 
     * 
     * @abstract
     * @access public
     * @return void
     */
    public abstract function testGenerateAndInsert();

    /**
     * getImporter 
     * 
     * @param string $databaseName 
     * @param string $tableName 
     * @access protected
     * @return TableImporter $importer
     */
    protected function getImporter($databaseName, $tableName)
    {
        $generator = new TableImporterGenerator($databaseName, $tableName, $this->getTargetPath());
        $generator->generate();
        $className = ucfirst($databaseName) . ucfirst($tableName) . 'Importer';
        $importerPath = $this->getTargetPath(). DIRECTORY_SEPARATOR . $databaseName . DIRECTORY_SEPARATOR . $className . '.php';
        require_once($importerPath);
        return $className::create();
    }

    /**
     * insertTest 
     * 
     * @param TableImporter $importer 
     * @param array $values 
     * @param array $ngValues 
     * @access protected
     * @return void
     */
    protected function insertTest(TableImporter $importer, array $values = array(), array $ngValues = array())
    {
        $data = $importer->addRow($values, $ngValues);
        $this->assertSame(array_keys($data), $importer::getFields());
        foreach ($values as $key => $value) {
            $this->assertSame($data[$key], $value);
        }
        foreach ($ngValues as $key => $value) {
            $this->assertFalse(in_array($data[$key], $value));
        }
        return $data;
    }

}


