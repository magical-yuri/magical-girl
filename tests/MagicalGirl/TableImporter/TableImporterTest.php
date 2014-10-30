<?php
namespace Tests\MagicalGirl\TableImporter;

require_once(__DIR__ . '/../../../../../../lib/DB/DBConnections.php');
use MagicalGirl\TableImporter\TableImporter;

abstract class TableImporterTest extends \PHPUnit_Framework_TestCase
{
    protected $con;
    protected $importer;

    protected function setUp()
    {
        $databaseName = strtolower(preg_replace('/^(.+?)[A-Z].*$/', '$1', get_class($this)));
        $this->con = \DBConnections::getConnection($databaseName);
    }

    /**
     * getImporter 
     * 
     * @param $option MagicalGirl\TableImporter\TableImporter::OPTION_XXXXXX
     * @abstract
     * @access protected
     * @return MagicalGirl\TableImporter\TableImporter
     */
    protected function getImporter($option = null)
    {
        $importerClass = preg_replace('/Test$/', '', get_class($this));
        if (is_null($option)) {
            return $importerClass::create();
        }
        return $importerClass::create($option);
    }


    /**
     * getDefaultValues 
     * 
     * @abstract
     * @access protected
     * @return array([fieldName] => [value], ...)
     */
    protected abstract function getDefaultValues();

    /**
     * getNgValues 
     * 
     * @abstract
     * @access protected
     * @return array([fieldName] => array([value],...)) 
     */
    protected abstract function getNgValues();

    public function testTruncateAtGenerateImporter()
    {
        $importer = $this->getImporter();
        $sql = 'select * from ' . $importer->getTableName();
        $stmt = $this->con->query($sql);
        $this->assertFalse((bool) $stmt->fetch());
        $stmt->closeCursor();

        $importer->addRow();
        
        $importer = $this->getImporter(TableImporter::OPTION_WITHOUT_TRUNCATE);
        $sql = 'select * from ' . $importer->getTableName();
        $stmt = $this->con->query($sql);
        $this->assertTrue((bool) $stmt->fetch());
        $stmt->closeCursor();
    }

    public function testAddRow()
    {
        $importer = $this->getImporter();
        $values = $this->getDefaultValues();
        $ngValues = $this->getNgValues();
        
        for ($i = 0; $i < 100; $i++) {
            $row = $importer->addRow($values, $ngValues);

            foreach ($values as $field => $value) {
                $this->assertSame($row[$field], $value);
            }

            foreach ($ngValues as $field => $value) {
                $this->assertFalse(in_array($row[$field], $value));
            }
        }
    }

}
